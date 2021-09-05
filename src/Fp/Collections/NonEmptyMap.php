<?php

declare(strict_types=1);

namespace Fp\Collections;

use Iterator;

/**
 * @template TK
 * @template-covariant TV
 * @psalm-immutable
 * @extends NonEmptyCollection<array{TK, TV}>
 * @extends NonEmptyMapOps<TK, TV>
 * @extends NonEmptyMapCasts<TK, TV>
 */
interface NonEmptyMap extends NonEmptyCollection, NonEmptyMapOps, NonEmptyMapCasts
{
    /**
     * @inheritDoc
     * @return Iterator<array{TK, TV}>
     */
    public function getIterator(): Iterator;
}
