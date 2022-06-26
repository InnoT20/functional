<?php

declare(strict_types=1);

namespace Fp\Operations;

use Generator;

use function Fp\Cast\asGenerator;

/**
 * @template TK
 * @template TV
 *
 * @extends AbstractOperation<TK, TV>
 */
final class UniqueOperation extends AbstractOperation
{
    /**
     * @param callable(TV): (int|string) $f
     * @return Generator<TK, TV>
     */
    public function __invoke(callable $f): Generator
    {
        return asGenerator(function () use ($f) {
            $hashTable = [];

            foreach ($this->gen as $key => $value) {
                $disc = $f($value);

                if (!array_key_exists($disc, $hashTable)) {
                    $hashTable[$disc] = true;
                    yield $key => $value;
                }
            }
        });
    }
}
