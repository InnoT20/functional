<?php

declare(strict_types=1);

namespace Fp\Operations;

use Generator;

use function Fp\Callable\asGenerator;

/**
 * @template TK
 * @template TV
 * @psalm-immutable
 * @extends AbstractOperation<TK, TV>
 */
class FilterNotNullOperation extends AbstractOperation
{
    /**
     * @psalm-pure
     * @return Generator<TK, TV>
     */
    public function __invoke(): Generator
    {
        return asGenerator(function () {
            foreach ($this->gen as $key => $value) {
                if (null !== $value) {
                    yield $key => $value;
                }
            }
        });
    }
}
