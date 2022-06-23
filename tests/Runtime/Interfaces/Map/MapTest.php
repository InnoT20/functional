<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Map;

use Fp\Collections\HashMap;
use Fp\Collections\NonEmptyArrayList;
use Fp\Collections\NonEmptyHashMap;
use Fp\Collections\NonEmptyHashSet;
use Fp\Collections\NonEmptyLinkedList;
use Fp\Functional\Option\Option;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Bar;
use Tests\Mock\Foo;

final class MapTest extends TestCase
{
    public function testCasts(): void
    {
        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toArray(),
        );

        $this->assertEquals(
            Option::some([['a', 1], ['b', 2]]),
            HashMap::collectPairs([['a', 1], ['b', 2]])->toNonEmptyArray(),
        );

        $this->assertEquals(
            Option::none(),
            HashMap::collectPairs([])->toNonEmptyArray(),
        );

        $this->assertEquals(
            [],
            HashMap::collectPairs([])->toAssocArray(),
        );
        $this->assertEquals(
            Option::none(),
            HashMap::collectPairs([])->toNonEmptyAssocArray(),
        );

        $this->assertEquals(
            ['a' => 1, 'b' => 2],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toAssocArray(),
        );

        $this->assertEquals(
            Option::some(['a' => 1, 'b' => 2]),
            HashMap::collectPairs([['a', 1], ['b', 2]])->toNonEmptyAssocArray(),
        );

        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toLinkedList()->toArray(),
        );

        $this->assertEquals(
            Option::some(NonEmptyLinkedList::collectNonEmpty([['a', 1], ['b', 2]])),
            HashMap::collectPairs([['a', 1], ['b', 2]])->toNonEmptyLinkedList(),
        );

        $this->assertEquals(
            Option::none(),
            HashMap::collectPairs([])->toNonEmptyLinkedList(),
        );

        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toArrayList()->toArray(),
        );

        $this->assertEquals(
            Option::some(NonEmptyArrayList::collectNonEmpty([['a', 1], ['b', 2]])),
            HashMap::collectPairs([['a', 1], ['b', 2]])->toNonEmptyArrayList(),
        );

        $this->assertEquals(
            Option::none(),
            HashMap::collectPairs([])->toNonEmptyArrayList(),
        );

        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toHashSet()->toArray(),
        );

        $this->assertEquals(
            Option::some(NonEmptyHashSet::collectNonEmpty([['a', 1], ['b', 2]])),
            HashMap::collectPairs([['a', 1], ['b', 2]])->toNonEmptyHashSet(),
        );

        $this->assertEquals(
            Option::none(),
            HashMap::collectPairs([])->toNonEmptyHashSet(),
        );

        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toHashMap()->toArray(),
        );

        $this->assertEquals(
            Option::some(NonEmptyHashMap::collectPairsNonEmpty([['a', 1], ['b', 2]])),
            HashMap::collectPairs([['a', 1], ['b', 2]])->toNonEmptyHashMap(),
        );

        $this->assertEquals(
            Option::none(),
            HashMap::collectPairs([])->toNonEmptyHashMap(),
        );
    }

    public function testContract(): void
    {
        $bar = new Bar(1);
        $hm = HashMap::collectPairs([[$bar, 'v1'], [new Foo(2), 'v2']]);
        $hm1 = HashMap::collectPairs([[[new Foo(1), new Foo(2)], 'v1']]);

        $this->assertEquals(
            'v2',
            $hm(new Foo(2))->get(),
        );

        $this->assertEquals(
            'v1',
            $hm1([new Foo(1), new Foo(2)])->get(),
        );

        $this->assertEquals(
            'v1',
            $hm($bar)->get(),
        );

        $this->assertNull($hm(new Bar(1))->get());
    }

    public function testCollisions(): void
    {
        $hm = HashMap::collectPairs([[1, 'v1'], [true, 'v2'], ['1', 'v3']]);
        $hm1 = HashMap::collectPairs([[0, 'v1'], [false, 'v2'], ['', 'v3']]);

        $this->assertEquals('v1', $hm(1)->get());
        $this->assertEquals('v2', $hm(true)->get());
        $this->assertEquals('v3', $hm('1')->get());

        $this->assertEquals('v1', $hm1(0)->get());
        $this->assertEquals('v2', $hm1(false)->get());
        $this->assertEquals('v3', $hm1('')->get());
    }

    public function testCount(): void
    {
        $this->assertEquals(2, HashMap::collectPairs([[1, 1], [2, 2]])->count());
    }
}
