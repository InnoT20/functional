<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Option\Option;
use Fp\Streams\Stream;

/**
 * @template-covariant TV
 */
interface SetCastableOps
{
    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toList();
     * => [1, 2]
     * ```
     *
     * @return list<TV>
     */
    public function toList(): array;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toNonEmptyList();
     * => Some([1, 2])
     * >>> HashSet::collect([])->toNonEmptyList();
     * => None
     * ```
     *
     * @return Option<non-empty-list<TV>>
     */
    public function toNonEmptyList(): Option;

    /**
     * ```php
     * >>> HashSet::collect([['fst', 1], ['snd', 2]])->toArray();
     * => ['fst' => 1, 'snd' => 2]
     * ```
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is Set<array{TKO, TVO}>
     *
     * @return array<TKO, TVO>
     */
    public function toArray(): array;

    /**
     * ```php
     * >>> HashSet::collect([['fst', 1], ['snd', 2]])->toNonEmptyArray();
     * => Some(['fst' => 1, 'snd' => 2])
     * >>> HashSet::collect([])->toNonEmptyArray();
     * => None
     * ```
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is Set<array{TKO, TVO}>
     *
     * @return Option<non-empty-array<TKO, TVO>>
     */
    public function toNonEmptyArray(): Option;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toLinkedList();
     * => LinkedList(1, 2)
     * ```
     *
     * @return LinkedList<TV>
     */
    public function toLinkedList(): LinkedList;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toNonEmptyLinkedList();
     * => Some(NonEmptyLinkedList(1, 2))
     * >>> HashSet::collect([])->toNonEmptyLinkedList();
     * => None
     * ```
     *
     * @return Option<NonEmptyLinkedList<TV>>
     */
    public function toNonEmptyLinkedList(): Option;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toArrayList();
     * => ArrayList(1, 2)
     * ```
     *
     * @return ArrayList<TV>
     */
    public function toArrayList(): ArrayList;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toNonEmptyArrayList();
     * => Some(NonEmptyArrayList(1, 2))
     * >>> HashSet::collect([])->toNonEmptyArrayList();
     * => None
     * ```
     *
     * @return Option<NonEmptyArrayList<TV>>
     */
    public function toNonEmptyArrayList(): Option;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toHashSet();
     * => HashSet(1, 2)
     * ```
     *
     * @return HashSet<TV>
     */
    public function toHashSet(): HashSet;

    /**
     * ```php
     * >>> HashSet::collect([1, 2, 2])->toNonEmptyHashSet();
     * => Some(NonEmptyHashSet(1, 2))
     * >>> HashSet::collect([])->toNonEmptyHashSet();
     * => None
     * ```
     *
     * @return Option<NonEmptyHashSet<TV>>
     */
    public function toNonEmptyHashSet(): Option;

    /**
     * ```php
     * >>> HashSet::collect([['one', 1], ['two', 2], ['two', 2]])->toHashMap();
     * => HashMap('one' -> 1, 'two' -> 2)
     * ```
     *
     * @template TKI
     * @template TVI
     * @psalm-if-this-is Set<array{TKI, TVI}>
     *
     * @return HashMap<TKI, TVI>
     */
    public function toHashMap(): HashMap;

    /**
     * ```php
     * >>> HashSet::collect([['one', 1], ['two', 2], ['two', 2]])->toNonEmptyHashMap();
     * => Some(NonEmptyHashMap('one' -> 1, 'two' -> 2))
     * >>> HashSet::collect([])->toNonEmptyHashMap();
     * => None
     * ```
     *
     * @template TKI
     * @template TVI
     * @psalm-if-this-is Set<array{TKI, TVI}>
     *
     * @return Option<NonEmptyHashMap<TKI, TVI>>
     */
    public function toNonEmptyHashMap(): Option;

    /**
     * ```php
     * >>> HashSet::collect(['fst', 'fst', 'snd', 'thd'])
     * >>>     ->toStream()
     * >>>     ->lines()
     * >>>     ->drain();
     * => 'fst'
     * => 'snd'
     * => 'thd'
     * ```
     *
     * @return Stream<TV>
     */
    public function toStream(): Stream;

    /**
     * If each element of Set is array then call of this method will fold all to the one array.
     *
     * ```php
     * >>> HashSet::collect([['fst' => 1], ['snd' => 2], ['thr' => 3]])->toMergedArray()
     * => ['fst' => 1, 'snd' => 2, 'thr' => 3]
     * ```
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is Set<array<TKO, TVO>>
     *
     * @return array<TKO, TVO>
     */
    public function toMergedArray(): array;
}
