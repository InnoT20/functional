<?php

declare(strict_types=1);

namespace Fp\Psalm\Hooks;

use Fp\Functional\Option\Option;
use Fp\Functional\Option\Some;
use Fp\Psalm\TypeRefinement\CollectionTypeParams;
use Fp\Psalm\TypeRefinement\GetPredicateFunction;
use Fp\Psalm\TypeRefinement\RefineByPredicate;
use Fp\Psalm\TypeRefinement\RefinementContext;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;

use function Fp\Collection\first;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class OptionFilterMethodReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [Option::class, Some::class];
    }

    /**
     * @psalm-suppress InternalMethod
     */
    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        $return_type = Option::do(function() use ($event) {
            yield proveTrue('filter' === $event->getMethodNameLowercase());

            $call_args = $event->getCallArgs();
            yield proveTrue(count($call_args) === 1);

            $source = yield proveOf($event->getSource(), StatementsAnalyzer::class);
            $option_type_param = yield first($event->getTemplateTypeParameters() ?? []);

            $collection_type_params = new CollectionTypeParams(
                key_type: Type::getArrayKey(),
                val_type: $option_type_param,
            );

            $predicate = yield GetPredicateFunction::from($call_args[0]);

            $refinement_context = new RefinementContext(
                refine_for: $event->getFqClasslikeName(),
                predicate: $predicate,
                execution_context: $event->getContext(),
                codebase: $source->getCodebase(),
                source: $source,
            );

            $result = RefineByPredicate::for($refinement_context, $collection_type_params);

            return new Type\Union([
                new Type\Atomic\TGenericObject(Option::class, [$result->collection_value_type]),
            ]);
        });

        return $return_type->get();
    }
}
