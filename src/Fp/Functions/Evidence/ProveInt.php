<?php

declare(strict_types=1);

namespace Fp\Evidence;

use Fp\Functional\Option\Option;

/**
 * Prove that subject is of integer type
 *
 * ```php
 * >>> proveInt(1.1);
 * => None
 *
 * >>> proveInt(1);
 * => Some(1)
 * ```
 *
 * @psalm-template T
 * @psalm-param T $potential
 * @psalm-return Option<int>
 * @psalm-pure
 */
function proveInt(mixed $potential): Option
{
    return Option::fromNullable(is_int($potential) ? $potential : null);
}
