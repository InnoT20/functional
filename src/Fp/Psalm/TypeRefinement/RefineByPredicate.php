<?php

declare(strict_types=1);

namespace Fp\Psalm\TypeRefinement;

use PhpParser\Node;
use Psalm\Type;
use Fp\Functional\Option\Option;
use Psalm\CodeLocation;
use Psalm\Internal\Algebra;
use Psalm\Internal\Algebra\FormulaGenerator;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Type\Reconciler;

use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

/**
 * @psalm-type CollectionTypeParameters = array{Type\Union, Type\Union}
 * @psalm-type PsalmAssertions = array<string, array<array<int, string>>>
 */
final class RefineByPredicate
{
    private const COLLECTION_TYPE = '$collection_type';

    /**
     * @psalm-return Option<RefinementResult>
     */
    public static function for(RefinementContext $context, CollectionTypeParams $collection_params): Option
    {
        return Option::do(function() use ($context, $collection_params) {
            $predicate_function = yield self::getPredicateFunction($context->predicate_arg);
            $predicate_arg_name = yield self::getPredicateArgumentName($predicate_function);
            $predicate_return_expr = yield self::getPredicateSingleReturn($predicate_function);

            $assertions = self::collectAssertions(
                context: $context,
                return_expr: $predicate_return_expr,
                predicate_arg_name: $predicate_arg_name,
            );

            $refined_val_type = self::refine(
                source: $context->source,
                assertions: $assertions,
                collection_type_param: $collection_params->val_type,
                return_expr: $predicate_return_expr,
            );

            return new RefinementResult(
                $collection_params->key_type,
                $refined_val_type->getOrElse($collection_params->val_type),
            );
        });
    }

    /**
     * Returns function if argument is Closure or ArrowFunction.
     *
     * @psalm-return Option<Node\Expr\Closure | Node\Expr\ArrowFunction>
     */
    private static function getPredicateFunction(Node\Arg $predicate_arg): Option
    {
        return Option::do(function() use ($predicate_arg) {
            yield proveTrue(
                $predicate_arg->value instanceof Node\Expr\Closure ||
                $predicate_arg->value instanceof Node\Expr\ArrowFunction
            );

            return $predicate_arg->value;
        });
    }

    /**
     * Returns argument name of $predicate that going to be refined.
     *
     * @psalm-return Option<non-empty-string>
     */
    private static function getPredicateArgumentName(Node\Expr\Closure|Node\Expr\ArrowFunction $predicate): Option
    {
        return Option::do(function() use ($predicate) {
            yield proveTrue(count($predicate->params) >= 1);
            $params = $predicate->params;

            return yield proveOf($params[0]->var, Node\Expr\Variable::class)
                ->flatMap(fn($variable) => proveString($variable->name))
                ->map(fn($name) => '$' . $name);
        });
    }

    /**
     * Returns single return expression of $predicate if present.
     * Collection type parameter can be refined only for function with single return.
     *
     * @psalm-return Option<Node\Expr>
     */
    private static function getPredicateSingleReturn(Node\Expr\Closure|Node\Expr\ArrowFunction $predicate): Option
    {
        return Option::do(function() use ($predicate) {
            $statements = $predicate->getStmts();
            yield proveTrue(1 === count($statements));

            return yield firstOf($statements, Node\Stmt\Return_::class)
                ->flatMap(fn($return_statement) => Option::fromNullable($return_statement->expr));
        });
    }

    /**
     * Collects assertion for $predicate_arg_name from $return_expr.
     *
     * @psalm-return PsalmAssertions
     */
    private static function collectAssertions(RefinementContext $context, Node\Expr $return_expr, string $predicate_arg_name): array
    {
        $cond_object_id = spl_object_id($return_expr);

        $filter_clauses = FormulaGenerator::getFormula(
            $cond_object_id,
            $cond_object_id,
            $return_expr,
            $context->execution_context->self,
            $context->source,
            $context->codebase
        );

        $assertions = [];

        foreach (Algebra::getTruthsFromFormula($filter_clauses, $cond_object_id) as $key => $assertion) {
            if (!str_starts_with($key, $predicate_arg_name)) {
                continue;
            }

            $assertions[str_replace($predicate_arg_name, self::COLLECTION_TYPE, $key)] = $assertion;
        }

        return $assertions;
    }

    /**
     * Reconciles $collection_type_param with $assertions using internal Psalm api.
     *
     * @psalm-param PsalmAssertions $assertions
     * @psalm-return Option<Type\Union>
     *
     * @psalm-suppress InternalMethod
     */
    private static function refine(StatementsAnalyzer $source, array $assertions, Type\Union $collection_type_param, Node\Expr $return_expr): Option
    {
        return Option::do(function() use ($source, $assertions, $collection_type_param, $return_expr) {
            yield proveTrue(!empty($assertions));

            // reconcileKeyedTypes takes it by ref
            $changed_var_ids = [];

            $reconciled_types = Reconciler::reconcileKeyedTypes(
                new_types: $assertions,
                active_new_types: $assertions,
                existing_types: [self::COLLECTION_TYPE => $collection_type_param],
                changed_var_ids: $changed_var_ids,
                referenced_var_ids: [self::COLLECTION_TYPE => true],
                statements_analyzer: $source,
                template_type_map: $source->getTemplateTypeMap() ?: [],
                inside_loop: false,
                code_location: new CodeLocation($source, $return_expr)
            );

            return yield Option::fromNullable($reconciled_types[self::COLLECTION_TYPE] ?? null);
        });
    }
}
