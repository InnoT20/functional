<?php

declare(strict_types=1);

namespace Fp\Operations;

use Generator;

/**
 * @template TK
 * @template TV
 *
 * @extends AbstractOperation<TK, TV>
 */
final class DropOperation extends AbstractOperation
{
    /**
     * @return Generator<TK, TV>
     */
    public function __invoke(int $length): Generator
    {
        $i = 0;

        foreach ($this->gen as $key => $value) {
            if ($i < $length) {
                $i++;
                continue;
            }

            yield $key => $value;
        }
    }
}
