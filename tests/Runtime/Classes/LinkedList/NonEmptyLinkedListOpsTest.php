<?php

declare(strict_types=1);

namespace Tests\Runtime\Classes\LinkedList;

use Fp\Collections\NonEmptyLinkedList;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Bar;
use Tests\Mock\Foo;
use Tests\Mock\SubBar;

use function Fp\Cast\asList;

final class NonEmptyLinkedListOpsTest extends TestCase
{
    public function testAppendPrepend(): void
    {
        $linkedList = NonEmptyLinkedList::collectNonEmpty([1, 2, 3]);
        $linkedList = $linkedList->prepend(0)->append(4);

        $list = asList($linkedList);

        $this->assertEquals(
            [0, 1, 2, 3, 4],
            $list,
        );
    }

    public function testAt(): void
    {
        $linkedList = NonEmptyLinkedList::collectNonEmpty([0, 1, 2, 3, 4, 5]);

        $this->assertEquals(0, $linkedList->at(0)->getUnsafe());
        $this->assertEquals(3, $linkedList->at(3)->getUnsafe());
        $this->assertEquals(5, $linkedList->at(5)->getUnsafe());
    }

    public function testEvery(): void
    {
        $linkedList = NonEmptyLinkedList::collectNonEmpty([0, 1, 2, 3, 4, 5]);

        $this->assertTrue($linkedList->every(fn($i) => $i >= 0));
        $this->assertFalse($linkedList->every(fn($i) => $i > 0));
    }

    public function testEveryOf(): void
    {
        $linkedList0 = NonEmptyLinkedList::collectNonEmpty([new Foo(1), new Foo(1)]);
        $linkedList1 = NonEmptyLinkedList::collectNonEmpty([new Bar(true), new Foo(1)]);

        $this->assertTrue($linkedList0->everyOf(Foo::class));
        $this->assertFalse($linkedList1->everyOf(Foo::class));
    }

    public function testExists(): void
    {
        /** @var NonEmptyLinkedList<mixed> $linkedList */
        $linkedList = NonEmptyLinkedList::collectNonEmpty([new Foo(1), 1, new Foo(1)]);

        $this->assertTrue($linkedList->exists(fn($i) => $i === 1));
        $this->assertFalse($linkedList->exists(fn($i) => $i === 2));
    }

    public function testExistsOf(): void
    {
        $linkedList = NonEmptyLinkedList::collectNonEmpty([1, new Foo(1)]);

        $this->assertTrue($linkedList->existsOf(Foo::class));
        $this->assertFalse($linkedList->existsOf(Bar::class));
    }

    public function testFilter(): void
    {
        $linkedList = NonEmptyLinkedList::collectNonEmpty([new Foo(1), 1, new Foo(1)]);
        $this->assertEquals([1], $linkedList->filter(fn($i) => $i === 1)->toArray());
    }

    public function testFilterNotNull(): void
    {
        $linkedList = NonEmptyLinkedList::collectNonEmpty([1, null, 3]);
        $this->assertEquals([1, 3], $linkedList->filterNotNull()->toArray());
    }

    public function testFilterOf(): void
    {
        $bar = new Bar(1);
        $subBar = new SubBar(1);
        $linkedList = NonEmptyLinkedList::collectNonEmpty([new Foo(1), $bar, $subBar]);

        $this->assertEquals([$bar, $subBar], $linkedList->filterOf(Bar::class, false)->toArray());
        $this->assertEquals([$bar], $linkedList->filterOf(Bar::class, true)->toArray());
    }

    public function testFirst(): void
    {
        /** @var NonEmptyLinkedList<mixed> $linkedList */
        $linkedList = NonEmptyLinkedList::collectNonEmpty([new Foo(1), 2, 1, 3]);

        $this->assertEquals(1, $linkedList->first(fn($e) => 1 === $e)->get());
        $this->assertNull($linkedList->first(fn($e) => 5 === $e)->get());
    }

    public function testFirstOf(): void
    {
        $bar = new Bar(1);
        $subBar = new SubBar(1);
        $linkedList = NonEmptyLinkedList::collectNonEmpty([new Foo(1), $subBar, $bar]);

        $this->assertEquals($subBar, $linkedList->firstOf(Bar::class, false)->get());
        $this->assertEquals($bar, $linkedList->firstOf(Bar::class, true)->get());
    }

    public function testFlatMap(): void
    {
        $this->assertEquals(
            [1, 2, 3, 4, 5, 6],
            NonEmptyLinkedList::collectNonEmpty([2, 5])->flatMap(fn($e) => [$e - 1, $e, $e + 1])->toArray()
        );
    }

    public function testHead(): void
    {
        $this->assertEquals(
            2,
            NonEmptyLinkedList::collectNonEmpty([2, 3])->head()
        );
    }

    public function testLast(): void
    {
        $this->assertEquals(
            3,
            NonEmptyLinkedList::collectNonEmpty([2, 3, 0])->last(fn($e) => $e > 0)->get()
        );
    }

    public function testLastElement(): void
    {
        $this->assertEquals(
            0,
            NonEmptyLinkedList::collectNonEmpty([2, 3, 0])->lastElement()
        );
    }

    public function testMap(): void
    {
        $this->assertEquals(
            ['2', '3', '4'],
            NonEmptyLinkedList::collectNonEmpty([1, 2, 3])->map(fn($e) => (string) ($e + 1))->toArray()
        );
    }

    public function testReduce(): void
    {
        /** @var NonEmptyLinkedList<string> $list */
        $list = NonEmptyLinkedList::collectNonEmpty(['1', '2', '3']);

        $this->assertEquals(
            '123',
            $list->reduce(fn($acc, $e) => $acc . $e)
        );
    }

    public function testReverse(): void
    {
        $this->assertEquals(
            [3, 2, 1],
            NonEmptyLinkedList::collectNonEmpty([1, 2, 3])->reverse()->toArray()
        );
    }

    public function testTail(): void
    {
        $this->assertEquals(
            [2, 3],
            NonEmptyLinkedList::collectNonEmpty([1, 2, 3])->tail()->toArray()
        );
    }

    public function testUnique(): void
    {
        $foo1 = new Foo(1);
        $foo2 = new Foo(2);
        $this->assertEquals(
            [$foo1, $foo2],
            NonEmptyLinkedList::collectNonEmpty([$foo1, $foo1, $foo2])->unique(fn(Foo $e) => $e->a)->toArray()
        );
    }
}
