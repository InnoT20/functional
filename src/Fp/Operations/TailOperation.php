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
final class TailOperation extends AbstractOperation
{
    /**
     * @return Generator<TK, TV>
     */
    public function __invoke(): Generator
    {
        return asGenerator(function () {
            $isFirst = true;

            foreach ($this->gen as $key => $value) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                yield $key => $value;
            }
        });
    }
}
