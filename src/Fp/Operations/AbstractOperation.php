<?php

declare(strict_types=1);

namespace Fp\Operations;

use Generator;

use function Fp\Cast\asGenerator;

/**
 * @template TK
 * @template TV
 * @psalm-suppress InvalidTemplateParam
 * @psalm-consistent-constructor
 * @psalm-consistent-templates
 */
class AbstractOperation
{
    /**
     * @var Generator<TK, TV>
     */
    protected Generator $gen;

    /**
     *
     * @param iterable<TK, TV> $gen
     */
    final public function __construct(iterable $gen)
    {
        $this->gen = asGenerator(fn() => $gen);
    }

    /**
     * @template TKI
     * @template TVI
     * @param iterable<TKI, TVI> $input
     * @return static<TKI, TVI>
     */
    public static function of(iterable $input): static
    {
        return new static($input);
    }
}
