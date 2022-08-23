<?php

declare(strict_types=1);

namespace Fp\Streams;

use Fp\Collections\Map;
use Fp\Functional\Option\Option;
use Fp\Operations\FoldOperation;

/**
 * @template-covariant TV
 *
 * @psalm-suppress InvalidTemplateParam
 */
interface StreamTerminalOps
{
    /**
     * Returns true if every stream element satisfy the condition
     * and false otherwise
     *
     * ```php
     * >>> Stream::emits([1, 2])->every(fn($elem) => $elem > 0);
     * => true
     *
     * >>> Stream::emits([1, 2])->every(fn($elem) => $elem > 1);
     * => false
     * ```
     *
     * @param callable(TV): bool $predicate
     */
    public function every(callable $predicate): bool;

    /**
     * Returns true if every stream element is of given class
     * false otherwise
     *
     * ```php
     * >>> Stream::emits([new Foo(1), new Foo(2)])->everyOf(Foo::class);
     * => true
     *
     * >>> Stream::emits([new Foo(1), new Bar(2)])->everyOf(Foo::class);
     * => false
     * ```
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     */
    public function everyOf(string|array $fqcn, bool $invariant = false): bool;

    /**
     * Find if there is element which satisfies the condition
     *
     * ```php
     * >>> Stream::emits([1, 2])->exists(fn($elem) => 2 === $elem);
     * => true
     *
     * >>> Stream::emits([1, 2])->exists(fn($elem) => 3 === $elem);
     * => false
     * ```
     *
     * @param callable(TV): bool $predicate
     */
    public function exists(callable $predicate): bool;

    /**
     * Returns true if there is stream element of given class
     * False otherwise
     *
     * ```php
     * >>> Stream::emits([1, new Foo(2)])->existsOf(Foo::class);
     * => true
     *
     * >>> Stream::emits([1, new Foo(2)])->existsOf(Bar::class);
     * => false
     * ```
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     */
    public function existsOf(string|array $fqcn, bool $invariant = false): bool;

    /**
     * Produces a new Map of elements by assigning the values to keys generated by a transformation function (callback).
     *
     * ```php
     * >>> Stream::emits([1, 2, 3])->reindex(fn($v) => "key-{$v}");
     * => HashMap('key-1' -> 1, 'key-2' -> 2, 'key-3' -> 3)
     * ```
     *
     * @template TKO
     *
     * @param callable(TV): TKO $callback
     * @return Map<TKO, TV>
     */
    public function reindex(callable $callback): Map;

    /**
     * Find first element which satisfies the condition
     *
     * ```php
     * >>> Stream::emits([1, 2, 3])->first(fn($elem) => $elem > 1)->get();
     * => 2
     * ```
     *
     * @param callable(TV): bool $predicate
     * @return Option<TV>
     */
    public function first(callable $predicate): Option;

    /**
     * Find first element of given class
     *
     * ```php
     * >>> Stream::emits([new Bar(1), new Foo(2), new Foo(3)])->firstOf(Foo::class)->get();
     * => Foo(2)
     * ```
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     * @return Option<TVO>
     */
    public function firstOf(string|array $fqcn, bool $invariant = false): Option;

    /**
     * Fold many elements into one
     *
     * ```php
     * >>> Stream::emits(['1', '2'])->fold('0')(fn($acc, $cur) => $acc . $cur);
     * => '012'
     * ```
     *
     * @template TVO
     *
     * @param TVO $init
     * @return FoldOperation<TV, TVO>
     *
     * @see FoldMethodReturnTypeProvider
     */
    public function fold(mixed $init): FoldOperation;

    /**
     * Return first stream element
     *
     * ```php
     * >>> Stream::emits([1, 2])->head()->get();
     * => 1
     * ```
     *
     * @return Option<TV>
     */
    public function head(): Option;

    /**
     * Returns last stream element which satisfies the condition
     *
     * ```php
     * >>> Stream::emits([1, 0, 2])->last(fn($elem) => $elem > 0)->get();
     * => 2
     * ```
     *
     * @param callable(TV): bool $predicate
     * @return Option<TV>
     */
    public function last(callable $predicate): Option;

    /**
     * Returns first stream element
     * Alias for {@see SeqOps::head}
     *
     * ```php
     * >>> Stream::emits([1, 2])->firstElement()->get();
     * => 1
     * ```
     *
     * @return Option<TV>
     */
    public function firstElement(): Option;

    /**
     * Returns last stream element
     *
     * ```php
     * >>> Stream::emits([1, 2])->lastElement()->get();
     * => 2
     * ```
     *
     * @return Option<TV>
     */
    public function lastElement(): Option;

    /**
     * Run the stream.
     *
     * This is useful if you care only for side effects.
     *
     * ```php
     * >>> Stream::drain([1, 2])->lines()->drain();
     * 1
     * 2
     * ```
     */
    public function drain(): void;

    /**
     * Displays all elements of this collection in a string
     * using start, end, and separator strings.
     *
     * ```php
     * >>> Stream::emits([1, 2, 3])->mkString("(", ",", ")")
     * => '(1,2,3)'
     *
     * >>> Stream::emits([])->mkString("(", ",", ")")
     * => '()'
     * ```
     */
    public function mkString(string $start = '', string $sep = ',', string $end = ''): string;
}
