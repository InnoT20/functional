<?php

declare(strict_types=1);

namespace Fp\Function\Collection;

/**
 * @psalm-template TK of array-key
 * @psalm-template TV
 *
 * @psalm-param iterable<TK, TV> $collection
 * @psalm-param callable(TV, TK): array-key $callback
 *
 * @psalm-return array<array-key, array<TK, TV>>
 */
function group(iterable $collection, callable $callback): array
{
    $groups = [];

    foreach ($collection as $index => $element) {
        $groupKey = call_user_func($callback, $element, $index);

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [];
        }

        $groups[$groupKey][$index] = $element;
    }

    return $groups;
}

