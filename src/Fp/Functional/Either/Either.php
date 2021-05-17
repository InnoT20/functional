<?php

declare(strict_types=1);

namespace Fp\Functional\Either;

use Fp\Functional\Option\None;
use Fp\Functional\Option\Option;
use Fp\Functional\Option\Some;
use Generator;
use Throwable;

/**
 * @template-covariant L
 * @template-covariant R
 * @psalm-yield R
 * @psalm-immutable
 */
abstract class Either
{
    /**
     * @psalm-template F
     * @psalm-param F $fallback
     * @psalm-return R|F
     */
    public function getOrElse(mixed $fallback): mixed
    {
        return $this->isRight() ? $this->value : $fallback;
    }

    /**
     * @psalm-template TO
     * @psalm-param \Closure(R): TO $ifRight
     * @psalm-param \Closure(L): TO $ifLeft
     * @psalm-return TO
     */
    public function fold(\Closure $ifRight, \Closure $ifLeft): mixed
    {
        if ($this->isRight()) {
            return $ifRight($this->value);
        } else {
            /**
             * @var Left<L, R> $this
             */

            return $ifLeft($this->value);
        }
    }

    /**
     * @psalm-template RO
     * @psalm-param \Closure(R): RO $closure
     * @psalm-return Either<L, RO>
     */
    public function map(\Closure $closure): Either
    {
        if ($this->isLeft()) {
            return new Left($this->value);
        }

        /**
         * @var Right<L, R> $this
         */

        return new Right($closure($this->value));
    }

    /**
     * @psalm-template RO
     * @psalm-param \Closure(R): Either<L, RO> $closure
     * @psalm-return Either<L, RO>
     */
    public function flatMap(\Closure $closure): Either
    {
        if ($this->isLeft()) {
            return new Left($this->value);
        }

        /**
         * @var Right<L, R> $this
         */

        return $closure($this->value);
    }

    /**
     * @template TL
     * @template TR
     * @template TO
     * @psalm-param callable(): Generator<int, Either<TL, TR>, TR, TO> $computation
     * @psalm-return Either<TL, TO>
     */
    public static function do(callable $computation): Either {
        $generator = $computation();

        while ($generator->valid()) {
            $currentStep = $generator->current();

            if ($currentStep->isRight()) {
                $generator->send($currentStep->get());
            } else {
                /** @var Either<TL, TO> $currentStep */
                return $currentStep;
            }

        }

        return new Right($generator->getReturn());
    }

    /**
     * @psalm-template TLI of Throwable
     * @psalm-template TRI
     * @psalm-param callable(): TRI $callback
     * @psalm-return Either<TLI, TRI>
     */
    public static function try(callable $callback): Either
    {
        try {
            /** @var Right<TLI, TRI> $r */
            $r = Right::of(call_user_func($callback));

        } catch (Throwable $exception) {
            /** @var Left<TLI, TRI> $r */
            $r = Left::of($exception);
        }

        return $r;
    }

    /**
     * @psalm-return Option<R>
     */
    public function toOption(): Option
    {
        return $this->isRight() ? new Some($this->value) : new None();
    }

    /**
     * @psalm-assert-if-true Left<L, R> $this
     */
    public function isLeft(): bool
    {
        return $this instanceof Left;
    }

    /**
     * @psalm-assert-if-true Right<L, R> $this
     */
    public function isRight(): bool
    {
        return $this instanceof Right;
    }

    /**
     * @psalm-return Either<R, L>
     */
    public function swap(): Either
    {
        return match (true) {
            ($this instanceof Right) => new Left($this->value),
            ($this instanceof Left) => new Right($this->value),
        };
    }

    /**
     * @psalm-template LI
     * @psalm-param LI $value
     * @psalm-return Left<LI, empty>
     */
    public static function left(int|float|bool|string|object|array $value): Left
    {
        return Left::of($value);
    }

    /**
     * @psalm-template RI
     * @psalm-param RI $value
     * @psalm-return Right<empty, RI>
     */
    public static function right(int|float|bool|string|object|array $value): Right
    {
        return Right::of($value);
    }

    /**
     * @psalm-return L|R
     */
    abstract public function get(): int|float|bool|string|object|array;
}
