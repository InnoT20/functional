<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Streams\Stream;

/**
 * @template TK
 * @template-covariant TV
 */
interface NonEmptyMapCastableOps
{
    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toList();
     * => [['a', 1], ['b', 2]]
     * ```
     *
     * @return list<array{TK, TV}>
     */
    public function toList(): array;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toList();
     * => [['a', 1], ['b', 2]]
     * ```
     *
     * @return non-empty-list<array{TK, TV}>
     */
    public function toNonEmptyList(): array;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectPairsNonEmpty([['a',  1], ['b', 2]])->toArray();
     * => ['a' => 1, 'b' => 2]
     * ```
     *
     * @return array<TK, TV>
     * @psalm-return (TK is array-key ? array<TK, TV> : never)
     */
    public function toArray(): array;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectPairsNonEmpty([['a',  1], ['b', 2]])->toNonEmptyArray();
     * => Some(['a' => 1, 'b' => 2])
     * >>> HashMap::collectPairs([])->toNonEmptyArray();
     * => None
     * ```
     *
     * @return non-empty-array<TK, TV>
     * @psalm-return (TK is array-key ? non-empty-array<TK, TV> : never)
     */
    public function toNonEmptyArray(): array;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toLinkedList();
     * => LinkedList(['a', 1], ['b', 2])
     * ```
     *
     * @return LinkedList<array{TK, TV}>
     */
    public function toLinkedList(): LinkedList;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toNonEmptyLinkedList();
     * => NonEmptyLinkedList(['a', 1], ['b', 2])
     * ```
     *
     * @return NonEmptyLinkedList<array{TK, TV}>
     */
    public function toNonEmptyLinkedList(): NonEmptyLinkedList;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toArrayList();
     * => ArrayList(['a', 1], ['b', 2])
     * ```
     *
     * @return ArrayList<array{TK, TV}>
     */
    public function toArrayList(): ArrayList;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toNonEmptyArrayList();
     * => NonEmptyArrayList(['a', 1], ['b', 2])
     * ```
     *
     * @return NonEmptyArrayList<array{TK, TV}>
     */
    public function toNonEmptyArrayList(): NonEmptyArrayList;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toHashSet();
     * => HashSet(['a', 1], ['b', 2])
     * ```
     *
     * @return HashSet<array{TK, TV}>
     */
    public function toHashSet(): HashSet;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toNonEmptyHashSet();
     * => NonEmptyHashSet(['a', 1], ['b', 2])
     * ```
     *
     * @return NonEmptyHashSet<array{TK, TV}>
     */
    public function toNonEmptyHashSet(): NonEmptyHashSet;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toHashMap();
     * => HashMap('a' -> 1, 'b' -> 2)
     * ```
     *
     * @return HashMap<TK, TV>
     */
    public function toHashMap(): HashMap;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectNonEmpty(['a' => 1, 'b' => 2])->toNonEmptyHashMap();
     * => NonEmptyHashMap('a' -> 1, 'b' -> 2)
     * ```
     *
     * @return NonEmptyHashMap<TK, TV>
     */
    public function toNonEmptyHashMap(): NonEmptyHashMap;

    /**
     * ```php
     * >>> NonEmptyHashMap::collectPairsNonEmpty([['fst', 1], ['snd', 2], ['thr', 3]])
     * >>>     ->toStream()
     * >>>     ->lines()
     * >>>     ->drain();
     * => Array([0] => fst, [1] => 1)
     * => Array([0] => snd, [1] => 2)
     * => Array([0] => thr, [1] => 3)
     * ```
     *
     * @return Stream<array{TK, TV}>
     */
    public function toStream(): Stream;
}
