<?php

declare(strict_types=1);

namespace Fp\Collection;

use Fp\Operations\UniqueOperation;

use function Fp\Cast\asList;

/**
 * Returns collection unique elements
 *
 * ```php
 * >>> unique([1, 2, 2, 3, 3, 3, 3]);
 * => [1, 2, 3]
 *
 * >>> unique(
 *     [new User(id: 1), new User(id: 1), new User(id: 2)],
 *     fn(User $user) => $user->getId()
 * );
 * => [User(1), User(2)]
 * ```
 *
 * @template TK of array-key
 * @template TV
 *
 * @param iterable<TK, TV> $collection
 * @param callable(TV): array-key $callback returns element unique id
 * @return list<TV>
 */
function unique(iterable $collection, callable $callback): array
{
    return asList(UniqueOperation::of($collection)($callback));
}
