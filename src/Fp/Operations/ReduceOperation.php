<?php

declare(strict_types=1);

namespace Fp\Operations;

use Fp\Functional\Option\Option;

/**
 * @template TK
 * @template TV
 *
 * @extends AbstractOperation<TK, TV>
 */
final class ReduceOperation extends AbstractOperation
{
    /**
     * @template TA
     *
     * @param callable(TV|TA, TV): (TV|TA) $f
     * @return Option<TV|TA>
     */
    public function __invoke(callable $f): Option
    {
        /** @var TV|TA $acc */
        $acc = null;
        $toggle = true;

        foreach ($this->gen as $value) {
            if ($toggle) {
                $acc = $value;
                $toggle = false;
                continue;
            }

            $acc = $f($acc, $value);
        }

        return Option::fromNullable($acc);
    }
}
