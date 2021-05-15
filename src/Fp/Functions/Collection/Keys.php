<?php

declare(strict_types=1);

namespace Fp\Collection;

/**
 * Returns list of collection keys
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 *
 * @psalm-param iterable<TK, TV> $collection
 *
 * @psalm-return list<int|string>
 */
function keys(iterable $collection): array
{
    $keys = [];

    foreach ($collection as $index => $element) {
        $keys[] = $index;
    }

    return $keys;
}
