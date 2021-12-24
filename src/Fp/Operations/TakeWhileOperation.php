<?php

declare(strict_types=1);

namespace Fp\Operations;

use Generator;

use function Fp\Cast\asGenerator;

/**
 * @template TK
 * @template TV
 * @psalm-immutable
 * @extends AbstractOperation<TK, TV>
 */
class TakeWhileOperation extends AbstractOperation
{
    /**
     * @psalm-pure
     * @template TKO
     * @psalm-param callable(TV, TK): bool $f
     * @return Generator<TK, TV>
     */
    public function __invoke(callable $f): Generator
    {
        return asGenerator(function () use ($f) {
            foreach ($this->gen as $key => $value) {
                if (!$f($value, $key)) {
                    break;
                }

                yield $key => $value;
            }
        });
    }
}
