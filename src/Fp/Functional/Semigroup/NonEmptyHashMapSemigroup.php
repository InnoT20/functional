<?php

declare(strict_types=1);

namespace Fp\Functional\Semigroup;

use Fp\Collections\NonEmptyHashMap;

use function Fp\Callable\asGenerator;

/**
 * @template TK
 * @template TV
 * @psalm-immutable
 * @extends Semigroup<NonEmptyHashMap<TK, TV>>
 */
class NonEmptyHashMapSemigroup extends Semigroup
{
    /**
     * @psalm-pure
     * @psalm-param NonEmptyHashMap<TK, TV> $lhs
     * @psalm-param NonEmptyHashMap<TK, TV> $rhs
     * @psalm-return NonEmptyHashMap<TK, TV>
     */
    public function combine(mixed $lhs, mixed $rhs): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairsUnsafe(asGenerator(function () use ($rhs, $lhs) {
            foreach ($lhs as $pair) {
                yield $pair;
            }
            foreach ($rhs as $pair) {
                yield $pair;
            }
        }));
    }
}
