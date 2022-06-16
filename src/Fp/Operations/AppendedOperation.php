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
class AppendedOperation extends AbstractOperation
{
    /**
     * @template TVI
     * @psalm-param TVI $elem
     * @return Generator<TV|TVI>
     */
    public function __invoke(mixed $elem): Generator
    {
        return asGenerator(function () use ($elem) {
            foreach ($this->gen as $prefixElem) {
                yield $prefixElem;
            }

            yield $elem;
        });
    }
}
