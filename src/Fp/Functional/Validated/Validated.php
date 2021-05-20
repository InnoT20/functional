<?php

declare(strict_types=1);

namespace Fp\Functional\Validated;

/**
 * @template-covariant E
 * @template-covariant A
 * @psalm-yield A
 * @psalm-immutable
 */
abstract class Validated
{
    /**
     * @psalm-template EE
     * @psalm-template AA
     *
     * @psalm-param EE $value
     *
     * @psalm-return Invalid<EE, AA>
     */
    public static function invalid(mixed $value): Invalid
    {
        return new Invalid([$value]);
    }

    /**
     * @psalm-template EE
     * @psalm-template AA
     *
     * @psalm-param AA $value
     *
     * @psalm-return Valid<EE, AA>
     */
    public static function valid(mixed $value): Valid
    {
        return new Valid([$value]);
    }

    /**
     * @psalm-return non-empty-list<E>|non-empty-list<A>
     */
    abstract public function get(): array;


    /**
     * @psalm-assert-if-true Valid<E, A> $this
     */
    public function isValid(): bool
    {
        return $this instanceof Valid;
    }

    /**
     * @psalm-assert-if-true Invalid<E, A> $this
     */
    public function isInvalid(): bool
    {
        return $this instanceof Invalid;
    }

    /**
     * @psalm-suppress all
     *
     * @psalm-template EE
     * @psalm-template AA
     *
     * @psalm-param Validated<EE, AA> $that
     *
     * @psalm-return Validated<E|EE, A|AA>
     */
    public function combine(Validated $that): Validated
    {
        if ($this->isValid() && $that->isValid()) {
            return new Valid(array_merge($this->value, $that->value));
        }

        if ($this->isInvalid() && $that->isInvalid()) {
            return new Invalid(array_merge($this->value, $that->value));
        }

        return $that->isInvalid()
            ? $that
            : $this;
    }


    /**
     * @psalm-template EE
     * @psalm-template AA
     * @psalm-param EE $left
     * @psalm-param AA $right
     * @psalm-return Validated<EE, AA>
     */
    public static function cond(
        bool $condition,
        mixed $valid,
        mixed $invalid,
    ): Validated
    {
        return $condition
            ? self::valid($valid)
            : self::invalid($invalid);
    }


    /**
     * @psalm-template B
     *
     * @param callable(non-empty-list<A>): B $ifValid
     * @param callable(non-empty-list<E>): B $ifInvalid
     *
     * @return B
     */
    public function fold(callable $ifValid, callable $ifInvalid): mixed
    {
        if ($this->isValid()) {
            return call_user_func($ifValid, $this->value);
        }

        /**
         * @var Invalid<E, A> $this
         */

        return call_user_func($ifInvalid, $this->value);
    }

}
