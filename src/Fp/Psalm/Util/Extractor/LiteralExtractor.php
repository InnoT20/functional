<?php

declare(strict_types=1);

namespace Fp\Psalm\Util\Extractor;

use Fp\Collections\ArrayList;
use Fp\Collections\NonEmptyHashSet;
use Fp\Collections\NonEmptySet;
use Fp\Functional\Option\Option;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Union;

/**
 * @internal
 */
trait LiteralExtractor
{
    /**
     * @psalm-return Option<NonEmptySet<int|float|string>>
     */
    public static function getUnionLiteralValues(Union $union): Option
    {
        $literalValues = ArrayList::collect($union->getLiteralStrings())
            ->appendedAll($union->getLiteralFloats())
            ->appendedAll($union->getLiteralInts())
            ->map(fn(TLiteralString|TLiteralFloat|TLiteralInt $literal) => $literal->value);

        return NonEmptyHashSet::collect($literalValues);
    }

    /**
     * @psalm-return Option<int|float|string>
     */
    public static function getUnionSingleLiteralValue(Union $union): Option
    {
        return self::getUnionSingleIntOrStringLiteralValue($union)
            ->orElse(fn() => self::getUnionSingleFloatLiteralValue($union));
    }

    /**
     * @psalm-return Option<int|string>
     */
    public static function getUnionSingleIntOrStringLiteralValue(Union $union): Option
    {
        return self::getUnionSingleIntLiteralValue($union)
            ->orElse(fn() => self::getUnionSingleStringLiteralValue($union));
    }

    /**
     * @psalm-return Option<int>
     */
    public static function getUnionSingleIntLiteralValue(Union $union): Option
    {
        return Option::some($union)
            ->filter(fn(Union $union) => $union->isSingleIntLiteral())
            ->flatMap(fn(Union $type) => self::getUnionLiteralValues($type))
            ->map(fn(NonEmptySet $literals) => $literals->head());
    }

    /**
     * @psalm-return Option<string>
     */
    public static function getUnionSingleStringLiteralValue(Union $union): Option
    {
        return Option::some($union)
            ->filter(fn(Union $union) => $union->isSingleStringLiteral())
            ->flatMap(fn(Union $type) => self::getUnionLiteralValues($type))
            ->map(fn(NonEmptySet $literals) => $literals->head());
    }

    /**
     * @psalm-return Option<float>
     */
    public static function getUnionSingleFloatLiteralValue(Union $union): Option
    {
        return Option::some($union)
            ->filter(fn(Union $union) => $union->isSingleFloatLiteral())
            ->flatMap(fn(Union $type) => self::getUnionLiteralValues($type))
            ->map(fn(NonEmptySet $literals) => $literals->head());
    }
}
