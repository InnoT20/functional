<?php

declare(strict_types=1);

namespace Fp\Collection;

use Fp\Operations\TailOperation;

use function Fp\Cast\asList;

/**
 * Returns every collection element except first
 *
 * ```php
 * >>> tail([1, 2, 3]);
 * => [2, 3]
 * ```
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 * @psalm-param iterable<TK, TV> $collection
 * @psalm-return list<TV>
 */
function tail(iterable $collection): array
{
    return asList(TailOperation::of($collection)());
}
