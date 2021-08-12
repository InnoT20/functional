<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Option\Option;

/**
 * @psalm-immutable
 * @template-covariant TV
 */
interface SeqOps
{
    /**
     * Returns true if there is collection element of given class
     * False otherwise
     *
     * @psalm-template TVO
     * @psalm-param class-string<TVO> $fqcn fully qualified class name
     * @psalm-param bool $invariant if turned on then subclasses are not allowed
     */
    function anyOf(string $fqcn, bool $invariant = false): bool;

    /**
     * Find element by its index
     * Returns None if there is no such collection element
     *
     * @psalm-return Option<TV>
     */
    function at(int $index): Option;

    /**
     * Returns true if every collection element satisfy the condition
     * false otherwise
     *
     * @psalm-param callable(TV): bool $predicate
     */
    function every(callable $predicate): bool;

    /**
     * Returns true if every collection element is of given class
     * false otherwise
     *
     * @psalm-template TVO
     * @psalm-param class-string<TVO> $fqcn fully qualified class name
     * @psalm-param bool $invariant if turned on then subclasses are not allowed
     */
    function everyOf(string $fqcn, bool $invariant = false): bool;

    /**
     * Find if there is element which satisfies the condition
     *
     * @psalm-param callable(TV): bool $predicate
     */
    function exists(callable $predicate): bool;

    /**
     * Filter collection by condition
     *
     * @psalm-param callable(TV): bool $predicate
     * @psalm-return Seq<TV>
     */
    function filter(callable $predicate): Seq;

    /**
     * Filter not null elements
     *
     * @psalm-return Seq<TV>
     */
    function filterNotNull(): Seq;

    /**
     * Filter elements of given class
     *
     * @psalm-template TVO
     * @psalm-param class-string<TVO> $fqcn fully qualified class name
     * @psalm-param bool $invariant if turned on then subclasses are not allowed
     * @psalm-return Seq<TVO>
     */
    function filterOf(string $fqcn, bool $invariant = false): Seq;

    /**
     * Find first element which satisfies the condition
     *
     * @psalm-param callable(TV, int): bool $predicate
     * @psalm-return Option<TV>
     */
    function first(callable $predicate): Option;

    /**
     * Find first element of given class
     *
     * @psalm-template TVO
     * @psalm-param class-string<TVO> $fqcn fully qualified class name
     * @psalm-param bool $invariant if turned on then subclasses are not allowed
     * @psalm-return Option<TVO>
     */
    function firstOf(string $fqcn, bool $invariant = false): Option;

    /**
     * @psalm-template TVO
     * @psalm-param callable(TV): iterable<TVO> $callback
     * @psalm-return Seq<TVO>
     */
    function flatMap(callable $callback): Seq;

    /**
     * Fold many elements into one
     *
     * @psalm-param TV $init initial accumulator value
     * @psalm-param callable(TV, TV): TV $callback (accumulator, current element): new accumulator
     * @psalm-return TV
     */
    function fold(mixed $init, callable $callback): mixed;

    /**
     * Do something for all collection elements
     *
     * @psalm-param callable(TV) $callback
     */
    function forAll(callable $callback): void;

    /**
     * @psalm-return Option<TV>
     */
    function head(): Option;

    /**
     * Returns last collection element which satisfies the condition
     *
     * @psalm-param null|callable(TV): bool $predicate
     * @psalm-return Option<TV>
     */
    function last(?callable $predicate = null): Option;

    /**
     * @template TVO
     * @psalm-param callable(TV): TVO $callback
     * @psalm-return Seq<TVO>
     */
    public function map(callable $callback): Seq;

    /**
     * Reduce multiple elements into one
     * Returns None for empty collection
     *
     * @psalm-param callable(TV, TV): TV $callback (accumulator, current value): new accumulator
     * @psalm-return Option<TV>
     */
    function reduce(callable $callback): Option;

    /**
     * Copy collection in reversed orderx
     *
     * @psalm-return Seq<TV>
     */
    function reverse(): Seq;

    /**
     * Returns every collection element except first
     *
     * @psalm-return Seq<TV>
     */
    function tail(): Seq;

    /**
     * Returns collection unique elements
     *
     * @psalm-param callable(TV): (int|string) $callback returns element unique id
     * @psalm-return Seq<TV>
     */
    function unique(callable $callback): Seq;
}
