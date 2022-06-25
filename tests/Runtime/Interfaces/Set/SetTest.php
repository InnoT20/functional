<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Set;

use Fp\Collections\ArrayList;
use Fp\Collections\HashMap;
use Fp\Collections\HashSet;
use Fp\Collections\LinkedList;
use Fp\Collections\NonEmptyArrayList;
use Fp\Collections\NonEmptyHashMap;
use Fp\Collections\NonEmptyHashSet;
use Fp\Collections\NonEmptyLinkedList;
use Fp\Functional\Option\Option;
use PHPUnit\Framework\TestCase;

final class SetTest extends TestCase
{
    public function testCasts(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            HashSet::collect([1, 2, 3, 3])->toArray(),
        );

        $this->assertEquals(
            Option::some([1, 2, 3]),
            HashSet::collect([1, 2, 3, 3])->toNonEmptyArray(),
        );

        $this->assertEquals(
            Option::none(),
            HashSet::collect([])->toNonEmptyArray(),
        );

        $this->assertEquals(
            LinkedList::collect([1, 2, 3]),
            HashSet::collect([1, 2, 3, 3])->toLinkedList(),
        );

        $this->assertEquals(
            Option::some(NonEmptyLinkedList::collectNonEmpty([1, 2, 3])),
            HashSet::collect([1, 2, 3, 3])->toNonEmptyLinkedList(),
        );

        $this->assertEquals(
            Option::none(),
            HashSet::collect([])->toNonEmptyLinkedList(),
        );

        $this->assertEquals(
            ArrayList::collect([1, 2, 3]),
            HashSet::collect([1, 2, 3, 3])->toArrayList(),
        );

        $this->assertEquals(
            Option::some(NonEmptyArrayList::collectNonEmpty([1, 2, 3])),
            HashSet::collect([1, 2, 3, 3])->toNonEmptyArrayList(),
        );

        $this->assertEquals(
            Option::none(),
            HashSet::collect([])->toNonEmptyArrayList(),
        );

        $this->assertEquals(
            HashSet::collect([1, 2, 3]),
            HashSet::collect([1, 2, 3, 3])->toHashSet(),
        );

        $this->assertEquals(
            Option::some(NonEmptyHashSet::collectNonEmpty([1, 2, 3])),
            HashSet::collect([1, 2, 3, 3])->toNonEmptyHashSet(),
        );

        $this->assertEquals(
            Option::none(),
            HashSet::collect([])->toNonEmptyHashSet(),
        );

        $this->assertEquals(
            HashMap::collectPairs([['fst', 1], ['snd', 2], ['thd', 3]]),
            HashSet::collect([['fst', 1], ['snd', 2], ['thd', 3]])->toHashMap(),
        );

        $this->assertEquals(
            Option::some(NonEmptyHashMap::collectPairsNonEmpty([['fst', 1], ['snd', 2], ['thd', 3]])),
            HashSet::collect([['fst', 1], ['snd', 2], ['thd', 3]])->toNonEmptyHashMap(),
        );

        $this->assertEquals(
            Option::none(),
            HashSet::collect([])->toNonEmptyHashMap(),
        );
    }

    public function testCount(): void
    {
        $this->assertCount(3, HashSet::collect([1, 2, 3]));
    }
}
