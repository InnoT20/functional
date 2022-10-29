<?php

declare(strict_types=1);

namespace Fp\Streams;

use Fp\Collections\Collection;
use Fp\Functional\Unit;

/**
 * @template-covariant TV
 *
 * @psalm-suppress InvalidTemplateParam
 */
interface StreamEmitter
{
    /**
     * Create singleton stream with one element
     *
     * ```php
     * >>> Stream::emit(1)->toList();
     * => [1]
     * ```
     *
     * @template TVI
     *
     * @param TVI $elem
     * @return Stream<TVI>
     */
    public static function emit(mixed $elem): Stream;

    /**
     * Emits elements from iterable source
     *
     * ```php
     * >>> Stream::emits([1, 2])->toList();
     * => [1, 2]
     * ```
     *
     * @template TVI
     *
     * @param (iterable<TVI>|Collection<TVI>) $source
     * @return Stream<TVI>
     */
    public static function emits(iterable $source): Stream;

    /**
     * Repeat this stream an infinite number of times.
     *
     * ```php
     * >>> Stream::emits([1,2,3])->repeat()->take(8)->toList();
     * => [1, 2, 3, 1, 2, 3, 1, 2]
     * ```
     *
     * If you pass $times stream repeats N times.
     *
     * ```php
     * >>> Stream::emit(1)->repeatN(3)->toList();
     * => [1, 1, 1]
     * ```
     *
     * @return Stream<TV>
     */
    public function repeat(null|int $times = null): Stream;

    /**
     * Discrete stream that emits elapsed duration since the start time of stream consumption.
     * For example: awakeEvery(5) will return (approximately) 5s, 10s, 15s, and will lie dormant between emitted values.
     *
     * @param 0|positive-int $seconds
     * @return Stream<int>
     */
    public static function awakeEvery(int $seconds): Stream;

    /**
     * Creates an infinite stream that always returns the supplied value
     *
     * ```php
     * >>> Stream::constant(0)->take(3)->toList();
     * => [0, 0, 0]
     * ```
     *
     * @template TVI
     *
     * @param TVI $const
     * @return Stream<TVI>
     */
    public static function constant(mixed $const): Stream;

    /**
     * Creates int stream of given range
     *
     * ```php
     * >>> Stream::range(0, 10, 2)->toList();
     * => [0, 2, 4, 6, 8]
     * ```
     *
     * @param positive-int $by
     * @return Stream<int>
     */
    public static function range(int $start, int $stopExclusive, int $by = 1): Stream;

    /**
     * Creates an infinite stream
     *
     * ```php
     * >>> Stream::infinite()->map(fn() => rand(0, 1))->take(2)->toList();
     * => [0, 1]
     * ```
     *
     * @return Stream<Unit>
     */
    public static function infinite(): Stream;
}
