<?php

declare(strict_types=1);

namespace Fp\Functional\Semigroup;

/**
 * @template T
 *
 * @extends Semigroup<T>
 * @psalm-immutable
 */
class RhsSemigroup extends Semigroup
{
    /**
     * @psalm-pure
     *
     * @psalm-param T $lhs
     * @psalm-param T $rhs
     *
     * @psalm-return T
     */
    public function combine(mixed $lhs, mixed $rhs): array
    {
        return $rhs;
    }
}
