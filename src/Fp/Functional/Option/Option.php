<?php

declare(strict_types=1);

namespace Fp\Functional\Option;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Generator;
use Throwable;

/**
 * @template-covariant A
 * @psalm-yield A
 * @psalm-immutable
 */
abstract class Option
{
    /**
     * @psalm-assert-if-false Some<A> $this
     */
    public function isEmpty(): bool
    {
        return $this instanceof None;
    }

    /**
     * @psalm-template B
     * @param callable(A): (B|null) $callback
     * @psalm-return Option<B>
     */
    public function map(callable $callback): Option
    {
        if ($this->isEmpty()) {
            return new None();
        }

        $value = $this->value;

        $result = call_user_func($callback, $value);

        return is_null($result) ? new None() : new Some($result);
    }

    /**
     * @psalm-template B
     * @param callable(A): Option<B> $callback
     * @psalm-return Option<B>
     */
    public function flatMap(callable $callback): Option
    {
        if ($this->isEmpty()) {
            return new None();
        }

        $value = $this->value;

        return call_user_func($callback, $value);
    }

    /**
     * @template TS
     * @template TO
     * @psalm-param callable(): Generator<int, Option<TS>, TS, TO> $computation
     * @psalm-return Option<TO>
     */
    public static function do(callable $computation): Option {
        $generator = $computation();

        while ($generator->valid()) {
            $currentStep = $generator->current();

            if (!$currentStep->isEmpty()) {
                $generator->send($currentStep->get());
            } else {
                /** @var Option<TO> $currentStep */
                return $currentStep;
            }

        }

        return Option::of($generator->getReturn());
    }

    /**
     * @psalm-template B
     * @param B|null $value
     * @psalm-return Option<B>
     * @psalm-pure
     */
    public static function of(mixed $value): Option
    {
        return is_null($value) ? new None() : new Some($value);
    }

    /**
     * @psalm-template B
     * @psalm-param (callable(): (B|null)) $callback
     * @psalm-return Option<B>
     */
    public static function try(callable $callback): Option
    {
        try {
            return self::of(call_user_func($callback));
        } catch (Throwable) {
            return new None();
        }
    }

    /**
     * @psalm-template B
     * @psalm-param callable(A): B $ifSome
     * @psalm-param callable(): B $ifNone
     * @psalm-return B
     */
    public function fold(callable $ifSome, callable $ifNone): mixed
    {
        return !$this->isEmpty()
            ? call_user_func($ifSome, $this->value)
            : $ifNone();
    }

    /**
     * @psalm-return A|null
     */
    public abstract function get(): mixed;

    /**
     * @psalm-template B
     * @psalm-param B $fallback
     * @psalm-return A|B
     */
    public function getOrElse(mixed $fallback): mixed
    {
        return !$this->isEmpty()
            ? $this->value
            : $fallback;
    }

    /**
     * Fabric method
     *
     * @psalm-template B
     * @psalm-param B $value
     * @psalm-return Option<B>
     * @psalm-pure
     */
    public static function some(int|float|bool|string|object|array $value): Option
    {
        return new Some($value);
    }

    /**
     * Fabric method
     *
     * @psalm-return Option<empty>
     * @psalm-pure
     */
    public static function none(): Option
    {
        return new None();
    }

    /**
     * @psalm-template B
     * @psalm-param callable(): B $right
     * @psalm-return Either<A, B>
     */
    public function toLeft(callable $right): Either
    {
        return !$this->isEmpty()
            ? new Left($this->value)
            : new Right(call_user_func($right));
    }

    /**
     * @psalm-template B
     * @psalm-param callable(): B $left
     * @psalm-return Either<B, A>
     */
    public function toRight(callable $left): Either
    {
        return !$this->isEmpty()
            ? new Right($this->value)
            : new Left(call_user_func($left));
    }
}
