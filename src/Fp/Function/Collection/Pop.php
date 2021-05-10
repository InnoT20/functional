<?php

declare(strict_types=1);

namespace Fp\Function\Collection;

use Fp\Functional\Option\Option;
use Fp\Functional\Tuple\Tuple2;

use function Fp\Function\Cast\asNonEmptyList;

/**
 * @psalm-template TK of array-key
 * @psalm-template TV
 *
 * @psalm-param iterable<TK, TV> $collection
 *
 * @psalm-return Option<Tuple2<TV, list<TV>>>
 */
function pop(iterable $collection): Option
{
    return asNonEmptyList($collection)
        ->map(fn($list) => Tuple2::ofArray([
            array_pop($list),
            $list
        ]));
}
