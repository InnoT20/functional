<?php

declare(strict_types=1);

namespace Fp\Functional\Tuple;

/**
 * @psalm-immutable
 * @template T1
 * @template T2
 * @template T3
 * @template T4
 * @template T5
 */
final class Tuple5
{
    /**
     * @param T1 $first
     * @param T2 $second
     * @param T3 $third
     * @param T4 $fourth
     * @param T5 $fifth
     */
    public function __construct(
        public mixed $first,
        public mixed $second,
        public mixed $third,
        public mixed $fourth,
        public mixed $fifth,
    ) {}

    /**
     * @psalm-template TI1
     * @psalm-template TI2
     * @psalm-template TI3
     * @psalm-template TI4
     * @psalm-template TI5
     * @psalm-param array{TI1,TI2,TI3,TI4,TI5} $tuple
     * @psalm-return self<TI1,TI2,TI3,TI4,TI5>
     */
    public static function ofArray(array $tuple): self
    {
        return new self(
            $tuple[0],
            $tuple[1],
            $tuple[2],
            $tuple[3],
            $tuple[4],
        );
    }

    /**
     * @return array{T1,T2,T3,T4,T5}
     */
    public function toArray(): array
    {
        return [
            $this->first,
            $this->second,
            $this->third,
            $this->fourth,
            $this->fifth,
        ];
    }
}
