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
final class MapOperation extends AbstractOperation
{
    /**
     * @template TVO
     *
     * @param callable(TK, TV): TVO $f
     * @return Generator<TK, TVO>
     */
    public function __invoke(callable $f): Generator
    {
        foreach ($this->gen as $key => $value) {
            yield $key => $f($key, $value);
        }
    }
}
