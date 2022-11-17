<?php

declare(strict_types=1);

namespace Fp\Psalm\Hook\FunctionReturnTypeProvider;

use Fp\Collections\ArrayList;
use Fp\Psalm\Util\GetCollectionTypeParams;
use Fp\Psalm\Util\TypeRefinement\CollectionTypeParams;
use Fp\Psalm\Util\TypeRefinement\PredicateExtractor;
use Fp\Psalm\Util\TypeRefinement\RefineByPredicate;
use Fp\Psalm\Util\TypeRefinement\RefineForEnum;
use Fp\Psalm\Util\TypeRefinement\RefinementContext;
use Fp\PsalmToolkit\Toolkit\CallArg;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use PhpParser\Node\Arg;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TList;
use Psalm\Type\Atomic\TNonEmptyList;
use Psalm\Type\Union;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Fp\Functional\Option\Option;

use function Fp\Callable\ctor;
use function Fp\Collection\first;
use function Fp\Collection\sequenceOptionT;
use function Fp\Evidence\proveOf;

final class FilterFunctionReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return [
            strtolower('Fp\Collection\filter'),
            strtolower('Fp\Collection\filterKV'),
            strtolower('Fp\Collection\last'),
            strtolower('Fp\Collection\first'),
        ];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Union
    {
        return PsalmApi::$args->getCallArgs($event)
            ->flatMap(fn(ArrayList $args) => sequenceOptionT(
                fn() => Option::some($event->getFunctionId() === 'fp\collection\filterkv'
                    ? RefineForEnum::KeyValue
                    : RefineForEnum::Value),
                fn() => PredicateExtractor::extract($event),
                fn() => Option::some($event->getContext()),
                fn() => proveOf($event->getStatementsSource(), StatementsAnalyzer::class),
                fn() => $args->firstMap(fn(CallArg $arg) => GetCollectionTypeParams::keyValue($arg->type)),
            ))
            ->mapN(ctor(RefinementContext::class))
            ->map(RefineByPredicate::for(...))
            ->map(fn(CollectionTypeParams $result) => self::getReturnType($event, $result))
            ->get();
    }

    private static function getReturnType(FunctionReturnTypeProviderEvent $event, CollectionTypeParams $result): Union
    {
        if (self::isAccessorFunction($event->getFunctionId())) {
            return self::optionType($result);
        }

        return first($event->getCallArgs())
            ->flatMap(fn(Arg $preserve_keys) => PsalmApi::$args->getArgType($event, $preserve_keys))
            ->flatMap(PsalmApi::$types->asSingleAtomic(...))
            ->map(fn($atomic) => $atomic::class === TList::class || $atomic::class === TNonEmptyList::class
                ? self::listType($result)
                : self::arrayType($result))
            ->getOrCall(fn() => self::listType($result));
    }

    private static function isAccessorFunction(string $id): bool
    {
        return $id === strtolower('Fp\Collection\last') || $id === strtolower('Fp\Collection\first');
    }

    private static function arrayType(CollectionTypeParams $result): Union
    {
        return new Union([
            new TArray([$result->key_type, $result->val_type]),
        ]);
    }

    private static function listType(CollectionTypeParams $result): Union
    {
        return new Union([
            new TList($result->val_type),
        ]);
    }

    private static function optionType(CollectionTypeParams $result): Union
    {
        return new Union([
            new TGenericObject(Option::class, [$result->val_type]),
        ]);
    }
}
