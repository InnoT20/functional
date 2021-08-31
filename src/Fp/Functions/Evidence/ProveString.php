<?php

declare(strict_types=1);

namespace Fp\Evidence;

use Fp\Functional\Option\Option;

/**
 * Prove that subject is of string type
 *
 * REPL:
 * >>> proveString('');
 * => Some<string>
 * >>> proveString(1);
 * => None
 *
 *
 * @psalm-template T
 *
 * @psalm-param T $potential
 *
 * @psalm-return Option<string>
 */
function proveString(mixed $potential): Option
{
    return Option::fromNullable(is_string($potential) ? $potential : null);
}

/**
 * Prove that subject is of class-string type
 *
 * REPL:
 * >>> proveClassString(Foo:class);
 * => Some<class-string>
 * >>> proveClassString('');
 * => None
 *
 *
 * @psalm-return Option<class-string>
 */
function proveClassString(mixed $potential): Option
{
    return Option::do(function () use ($potential) {
        $fqcn = yield proveString($potential);
        return yield Option::fromNullable(class_exists($fqcn) ? $fqcn : null);
    });
}

/**
 * Prove that subject is of non-empty-string type
 *
 * REPL:
 * >>> proveNonEmptyString('text');
 * => Some<non-empty-string>
 * >>> proveNonEmptyString('');
 * => None
 *
 *
 * @psalm-return Option<non-empty-string>
 */
function proveNonEmptyString(mixed $subject): Option
{
    return is_string($subject) && $subject !== ''
        ? Option::some($subject)
        : Option::none();
}

/**
 * Prove that subject is of callable-string type
 *
 * REPL:
 * >>> proveCallableString('array_map');
 * => Some<callable-string>
 * >>> proveCallableString('1');
 * => None
 *
 *
 * @psalm-return Option<callable-string>
 */
function proveCallableString(mixed $subject): Option
{
    return Option::do(function () use ($subject) {
        $string = yield proveString($subject);
        return yield Option::fromNullable(is_callable($string) ? $string : null);
    });
}


