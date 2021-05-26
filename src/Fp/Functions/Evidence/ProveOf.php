<?php

declare(strict_types=1);

namespace Fp\Evidence;

use Fp\Functional\Option\Option;

use function Fp\of;

/**
 * Prove that subject is of given class
 *
 * REPL:
 * >>> proveOf(new Foo(1), Foo::class);
 * => Some<Foo>
 * >>> proveOf(new Bar(2), Foo::class);
 * => None
 *
 *
 * @psalm-template TV
 * @psalm-template TVO
 *
 * @psalm-param TV $subject
 * @psalm-param class-string<TVO> $fqcn fully qualified class name
 *
 * @psalm-return Option<TVO>
 */
function proveOf(mixed $subject, string $fqcn, bool $invariant = false): Option
{
    /** @var Option<TVO> $option */
    $option = of($subject, $fqcn, $invariant)
        ? Option::some($subject)
        : Option::none();

    return $option;
}
