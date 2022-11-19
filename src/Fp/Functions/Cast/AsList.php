<?php

declare(strict_types=1);

namespace Fp\Cast;

/**
 * Copy one or multiple collections as list
 *
 * ```php
 * >>> asList([1], ['prop' => 2], [3, 4]);
 * => [1, 2, 3, 4]
 * ```
 *
 * @template TV
 *
 * @param iterable<mixed, TV> ...$collections
 * @return (
 *     $collections is non-empty-array
 *         ? non-empty-list<TV>
 *         : list<TV>
 * )
 *
 * @no-named-arguments
 */
function asList(iterable ...$collections): array
{
    $aggregate = [];

    foreach ($collections as $collection) {
        foreach ($collection as $element) {
            $aggregate[] = $element;
        }
    }

    return $aggregate;
}
