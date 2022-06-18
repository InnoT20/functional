<?php

declare(strict_types=1);

namespace Fp\Evidence;

use Fp\Functional\Option\Option;

/**
 * Prove that subject is of integer type
 *
 * ```php
 * >>> proveObject(1);
 * => None
 *
 * >>> proveObject(new Foo(1));
 * => Some(Foo(1))
 * ```
 *
 * @return Option<object>
 */
function proveObject(mixed $potential): Option
{
    return Option::fromNullable(is_object($potential) ? $potential : null);
}
