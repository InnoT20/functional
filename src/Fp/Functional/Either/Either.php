<?php

declare(strict_types=1);

namespace Fp\Functional\Either;

use Fp\Functional\Option\None;
use Fp\Functional\Option\Option;
use Fp\Functional\Option\Some;

/**
 * @template-covariant L
 * @template-covariant R
 * @psalm-immutable
 */
abstract class Either
{
    /**
     * @psalm-param Closure(): R $or
     * @psalm-return R
     */
    public function getOrElse(\Closure $or): mixed
    {
        return $this->isRight() ? $this->get() : $or();
    }

    /**
     * @template T
     * @psalm-param \Closure(L): T $ifLeft
     * @psalm-param \Closure(R): T $ifRight
     * @return T
     */
    public function fold(\Closure $ifLeft, \Closure $ifRight): mixed
    {
        if ($this->isRight()) {
            return $ifRight($this->get());
        } else {
            /**
             * @var Left<L, R> $this
             */

            return $ifLeft($this->get());
        }
    }

    /**
     * @psalm-template RO
     * @param \Closure(R): RO $closure
     * @psalm-return Either<L, RO>
     */
    public function map(\Closure $closure): Either
    {
        if ($this->isLeft()) {
            return new Left($this->get());
        }

        /**
         * @var Right<L, R> $this
         */

        return new Right($closure($this->get()));
    }

    /**
     * @psalm-template RO
     * @param \Closure(R): Either<L, RO> $closure
     * @psalm-return Either<L, RO>
     */
    public function flatMap(\Closure $closure): Either
    {
        if ($this->isLeft()) {
            return new Left($this->get());
        }

        /**
         * @var Right<L, R> $this
         */

        return $closure($this->get());
    }

    /**
     * @return Option<R>
     */
    public function toOption(): Option
    {
        return $this->isRight() ? new Some($this->get()) : new None();
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
}
