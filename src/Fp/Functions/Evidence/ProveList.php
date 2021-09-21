<?php

declare(strict_types=1);

namespace Fp\Evidence;

use Fp\Functional\Option\Option;

use function Fp\Collection\everyOf;
use function Fp\Collection\head;

/**
 * Prove that given collection is of list type
 *
 * ```php
 * >>> proveList([1, 2]);
 * => Some([1, 2])
 *
 * >>> proveList([1, 2 => 2]);
 * => None
 * ```
 *
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 *
 * @psalm-param iterable<TK, TV> $collection
 *
 * @psalm-return Option<list<TV>>
 */
function proveList(iterable $collection): Option
{
    return Option::do(function () use ($collection) {
        $array = yield proveArray($collection);
        $isSequence = true;
        $previousKey = -1;

        foreach (array_keys($array) as $key) {
            if (is_int($key) && is_int($previousKey) && 1 === ((int) $key - (int) $previousKey)) {
                $previousKey = $key;
            } else {
                $isSequence = false;
                break;
            }

        }

        yield proveTrue($isSequence);

        /** @var list<TV> */
        return $array;
    });
}

/**
 * Prove that given collection is of non-empty-list type
 *
 * ```php
 * >>> proveNonEmptyList([1, 2]);
 * => Some([1, 2])
 *
 * >>> proveNonEmptyList([1, 2 => 2]);
 * => None
 *
 * >>> proveNonEmptyList([]);
 * => None
 * ```
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 *
 * @psalm-param iterable<TK, TV> $collection
 *
 * @psalm-return Option<non-empty-list<TV>>
 */
function proveNonEmptyList(iterable $collection): Option
{
    return Option::do(function () use ($collection) {
        $list = yield proveList($collection);
        yield head($list);

        /** @var non-empty-list<TV> $list */
        return $list;
    });
}

/**
 * Prove that collection is of list type
 * and every element is of given class
 *
 * ```php
 * >>> proveListOf([new Foo(1), new Foo(2)], Foo::class);
 * => Some([Foo(1), Foo(2)])
 *
 * >>> proveListOf([new Foo(1), 2], Foo::class);
 * => None
 * ```
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 * @psalm-template TVO
 *
 * @psalm-param iterable<TK, TV> $collection
 * @psalm-param class-string<TVO> $fqcn fully qualified class name
 * @psalm-param bool $invariant if turned on then subclasses are not allowed
 *
 * @psalm-return Option<list<TVO>>
 */
function proveListOf(iterable $collection, string $fqcn, bool $invariant = false): Option
{
    return Option::do(function () use ($collection, $fqcn, $invariant) {
        $list = yield proveList($collection);
        yield proveTrue(everyOf($list, $fqcn, $invariant));

        /** @var list<TVO> $list */
        return $list;
    });
}

/**
 * Prove that collection is of non-empty-list type
 * and every element is of given class
 *
 * ```php
 * >>> proveNonEmptyListOf([new Foo(1), new Foo(2)], Foo::class);
 * => Some([Foo(1), Foo(2)])
 *
 * >>> proveNonEmptyListOf([new Foo(1), 2], Foo::class);
 * => None
 *
 * >>> proveNonEmptyListOf([], Foo::class);
 * => None
 * ```
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 * @psalm-template TVO
 *
 * @psalm-param iterable<TK, TV> $collection
 * @psalm-param class-string<TVO> $fqcn fully qualified class name
 * @psalm-param bool $invariant if turned on then subclasses are not allowed
 *
 * @psalm-return Option<non-empty-list<TVO>>
 */
function proveNonEmptyListOf(iterable $collection, string $fqcn, bool $invariant = false): Option
{
    return Option::do(function () use ($collection, $fqcn, $invariant) {
        $list = yield proveListOf($collection, $fqcn, $invariant);
        yield head($list);

        /** @var non-empty-list<TVO> $list */
        return $list;
    });
}

/**
 * Prove that collection is of list type
 * and every element is of given scalar type
 *
 * ```php
 * >>> proveListOfScalar([1, 2, 3], 'int');
 * => Some([1, 2, 3])
 *
 * >>> proveListOfScalar([1, '2', 3], 'int');
 * => None
 * ```
 *
 * @psalm-template TK of array-key
 * @psalm-template TV
 * @psalm-template TVO
 *
 * @psalm-param iterable<TK, TV> $collection
 * @psalm-param 'string'|'non-empty-string'|'int'|'float'|'bool' $type
 *
 * @psalm-return (
 *     $type is 'string'           ? Option<list<string>> : (
 *     $type is 'non-empty-string' ? Option<list<non-empty-string>> : (
 *     $type is 'int'              ? Option<list<int>> : (
 *     $type is 'float'            ? Option<list<float>> : (
 *                                   Option<list<bool>>
 * )))))
 */
function proveListOfScalar(mixed $subject, string $type): Option
{
    if (!is_array($subject)) {
        return Option::none();
    }

    return Option::do(function () use ($subject, $type) {
        $list = yield proveList($subject);

        $provenListOf = [];

        /** @psalm-var mixed $element */
        foreach ($list as $element) {
            $provenListOf[] = yield match($type) {
                'string' => proveString($element),
                'non-empty-string' => proveNonEmptyString($element),
                'int' => proveInt($element),
                'float' => proveFloat($element),
                'bool' => proveBool($element),
            };
        }

        return $provenListOf;
    });
}
