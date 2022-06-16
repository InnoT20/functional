<?php

declare(strict_types=1);

namespace Fp\Operations;

use Generator;

use function Fp\Cast\asGenerator;

/**
 * @template TK
 * @template TV
 * @psalm-suppress InvalidTemplateParam
 * @extends AbstractOperation<TK, TV>
 */
class DropOperation extends AbstractOperation
{
    /**
     * @return Generator<TK, TV>
     */
    public function __invoke(int $length): Generator
    {
        return asGenerator(function () use ($length) {
            $i = 0;

            foreach ($this->gen as $key => $value) {
                if ($i < $length) {
                    $i++;
                    continue;
                }

                yield $key => $value;
            }
        });
    }
}
