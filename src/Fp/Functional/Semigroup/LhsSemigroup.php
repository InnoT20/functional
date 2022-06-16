<?php

declare(strict_types=1);

namespace Fp\Functional\Semigroup;

/**
 * @template T
 * @psalm-suppress InvalidTemplateParam
 * @extends Semigroup<T>
 */
class LhsSemigroup extends Semigroup
{
    /**
     * @psalm-param T $lhs
     * @psalm-param T $rhs
     * @psalm-return T
     */
    public function combine(mixed $lhs, mixed $rhs): mixed
    {
        return $lhs;
    }
}
