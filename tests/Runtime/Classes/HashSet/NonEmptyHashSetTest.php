<?php

declare(strict_types=1);

namespace Tests\Runtime\Classes\HashSet;

use Fp\Collections\EmptyCollectionException;
use Fp\Collections\NonEmptyHashSet;
use Fp\Functional\Option\Option;
use PHPUnit\Framework\TestCase;

final class NonEmptyHashSetTest extends TestCase
{
    /**
     * @throws EmptyCollectionException
     */
    public function testCollect(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collect([1, 2, 3])->toArray(),
        );

        $catch = Option::try(fn() => NonEmptyHashSet::collect([]));
        $this->assertTrue($catch->isNone());
    }

    public function testCollectUnsafe(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectUnsafe([1, 2, 3])->toArray(),
        );

        $catch = Option::try(fn() => NonEmptyHashSet::collectUnsafe([]));
        $this->assertTrue($catch->isNone());
    }

    public function testCollectNonEmpty(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->toArray(),
        );
    }

    public function testCasts(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->toLinkedList()->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->toNonEmptyLinkedList()->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->toHashSet()->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            NonEmptyHashSet::collectNonEmpty([1, 2, 3])->toNonEmptyHashSet()->toArray(),
        );
    }
}
