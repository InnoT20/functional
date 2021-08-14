<?php

declare(strict_types=1);

namespace Fp\Collections;

/**
 * @template TK
 * @template-covariant TV
 * @psalm-immutable
 * @extends Collection<array{TK, TV}>
 * @extends MapOps<TK, TV>
 */
interface Map extends Collection, MapOps
{
    /**
     * @psalm-pure
     * @template TKI
     * @template TVI
     * @param iterable<array{TKI, TVI}> $source
     * @return self<TKI, TVI>
     */
    public static function collect(iterable $source): self;

    /**
     * @psalm-pure
     * @template TKI of array-key
     * @template TVI
     * @param iterable<TKI, TVI> $source
     * @return self<TKI, TVI>
     */
    public static function collectIterable(iterable $source): self;
}
