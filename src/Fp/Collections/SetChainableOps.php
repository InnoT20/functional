<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Option\Option;
use Fp\Psalm\Hook\MethodReturnTypeProvider\CollectionFilterMethodReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\MapTapNMethodReturnTypeProvider;

/**
 * @template-covariant TV
 *
 * @psalm-suppress InvalidTemplateParam
 */
interface SetChainableOps
{
    /**
     * Produces new set with given element included
     *
     * ```php
     * >>> HashSet::collect([1, 1, 2])->updated(3)->toList();
     * => [1, 2, 3]
     * ```
     *
     * @template TVI
     *
     * @param TVI $element
     * @return Set<TV|TVI>
     */
    public function appended(mixed $element): Set;

    /**
     * Add elements to the set
     *
     * ```php
     * >>> HashSet::collect([1, 2])->appendedAll([1, 2, 3])->toList();
     * => [1, 2, 3]
     * ```
     *
     * @template TVI
     *
     * @param (iterable<TVI>|Collection<TVI>) $that
     * @return Set<TV|TVI>
     */
    public function appendedAll(iterable $that): Set;

    /**
     * Produces new set with given element excluded
     *
     * ```php
     * >>> HashSet::collect([1, 1, 2])->removed(2)->toList();
     * => [1]
     * ```
     *
     * @param TV $element
     * @return Set<TV>
     */
    public function removed(mixed $element): Set;

    /**
     * Filter collection by condition
     *
     * ```php
     * >>> HashSet::collect([1, 2, 2])->filter(fn($elem) => $elem > 1)->toList();
     * => [2]
     * ```
     *
     * @param callable(TV): bool $predicate
     * @return Set<TV>
     *
     * @see CollectionFilterMethodReturnTypeProvider
     */
    public function filter(callable $predicate): Set;

    /**
     * @param callable(mixed...): bool $predicate
     * @return Set<TV>
     */
    public function filterN(callable $predicate): Set;

    /**
     * Filter elements of given class
     *
     * ```php
     * >>> HashSet::collect([1, 1, new Foo(2)])->filterOf(Foo::class)->toList();
     * => [Foo(2)]
     * ```
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     * @return Set<TVO>
     */
    public function filterOf(string|array $fqcn, bool $invariant = false): Set;

    /**
     * Exclude null elements
     *
     * ```php
     * >>> HashSet::collect([1, 1, null])->filterNotNull()->toList();
     * => [1]
     * ```
     *
     * @return Set<TV>
     */
    public function filterNotNull(): Set;

    /**
     * A combined {@see Set::map} and {@see Set::filter}.
     *
     * Filtering is handled via Option instead of Boolean.
     * So the output type TVO can be different from the input type TV.
     *
     * ```php
     * >>> HashSet::collect(['zero', '1', '2'])
     * >>>     ->filterMap(fn($elem) => is_numeric($elem) ? Option::some((int) $elem) : Option::none())
     * >>>     ->toList();
     * => [1, 2]
     * ```
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return Set<TVO>
     */
    public function filterMap(callable $callback): Set;

    /**
     * @template TVO
     *
     * @param callable(mixed...): Option<TVO> $callback
     * @return Set<TVO>
     */
    public function filterMapN(callable $callback): Set;

    /**
     * Converts this Set<iterable<TVO>> into a Set<TVO>.
     *
     * ```php
     * >>> HashSet::collect([
     * >>>     HashSet::collect([1, 2]),
     * >>>     HashSet::collect([3, 4]),
     * >>>     HashSet::collect([5, 6]),
     * >>> ])->flatten();
     * => HashSet(1, 2, 3, 4, 5, 6)
     * ```
     *
     * @template TVO
     * @psalm-if-this-is Set<iterable<TVO>|Collection<TVO>>
     *
     * @return Set<TVO>
     */
    public function flatten(): Set;

    /**
     * ```php
     * >>> HashSet::collect([2, 5, 5])->flatMap(fn($e) => [$e - 1, $e, $e, $e + 1])->toList();
     * => [1, 2, 3, 4, 5, 6]
     * ```
     *
     * @template TVO
     *
     * @param callable(TV): (iterable<TVO>|Collection<TVO>) $callback
     * @return Set<TVO>
     */
    public function flatMap(callable $callback): Set;

    /**
     * @template TVO
     *
     * @param callable(mixed...): (iterable<TVO>|Collection<TVO>) $callback
     * @return Set<TVO>
     */
    public function flatMapN(callable $callback): Set;

    /**
     * Produces a new collection of elements by mapping each element in collection
     * through a transformation function (callback)
     *
     * ```php
     * >>> HashSet::collect([1, 2, 2])->map(fn($elem) => (string) $elem)->toList();
     * => ['1', '2']
     * ```
     *
     * @template TVO
     *
     * @param callable(TV): TVO $callback
     * @return Set<TVO>
     */
    public function map(callable $callback): Set;

    /**
     * Same as {@see SetChainableOps::map()}, but deconstruct input tuple and pass it to the $callback function.
     *
     * @template TVO
     *
     * @param callable(mixed...): TVO $callback
     * @return Set<TVO>
     *
     * @see MapTapNMethodReturnTypeProvider
     */
    public function mapN(callable $callback): Set;

    /**
     * Call a function for every collection element
     *
     * ```php
     * >>> HashSet::collect([new Foo(1), new Foo(2)])
     * >>>     ->tap(fn(Foo $foo) => $foo->a = $foo->a + 1)
     * >>>     ->map(fn(Foo $foo) => $foo->a)
     * >>>     ->toList();
     * => [2, 3]
     * ```
     *
     * @param callable(TV): void $callback
     * @return Set<TV>
     */
    public function tap(callable $callback): Set;

    /**
     * @param callable(mixed...): void $callback
     * @return Set<TV>
     */
    public function tapN(callable $callback): Set;

    /**
     * Returns every collection element except first
     *
     * ```php
     * >>> HashSet::collect([1, 2, 3])->tail()->toList();
     * => [2, 3]
     * ```
     *
     * @return Set<TV>
     */
    public function tail(): Set;

    /**
     * Returns every collection element except last
     *
     * ```php
     * >>> HashSet::collect([1, 2, 3])->init()->toList();
     * => [1, 2]
     * ```
     *
     * @return Set<TV>
     */
    public function init(): Set;

    /**
     * Computes the intersection between this set and another set.
     *
     * ```php
     * >>> HashSet::collect([1, 2, 3])
     *     ->intersect(HashSet::collect([2, 3]))->toList();
     * => [2, 3]
     * ```
     *
     * @param Set<TV>|NonEmptySet<TV> $that the set to intersect with.
     * @return Set<TV>
     */
    public function intersect(Set|NonEmptySet $that): Set;

    /**
     * Computes the difference of this set and another set.
     *
     * ```php
     * >>> HashSet::collect([1, 2, 3])
     *     ->diff(HashSet::collect([2, 3]))->toList();
     * => [1]
     * ```
     *
     * @param Set<TV>|NonEmptySet<TV> $that the set of elements to exclude.
     * @return Set<TV> a set containing those elements of this
     * set that are not also contained in the given set `that`.
     */
    public function diff(Set|NonEmptySet $that): Set;
}
