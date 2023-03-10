<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Seq;

use Fp\Collections\ArrayList;
use Fp\Collections\HashMap;
use Fp\Collections\LinkedList;
use Fp\Collections\NonEmptyArrayList;
use Fp\Collections\NonEmptyLinkedList;
use Fp\Collections\NonEmptySeq;
use Fp\Collections\Seq;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Functional\Separated\Separated;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Foo;

final class SeqOpsTest extends TestCase
{
    /**
     * @return list<array{class-string<Seq>}>
     */
    public function seqClassDataProvider(): array
    {
        return [
            [ArrayList::class],
            [LinkedList::class],
        ];
    }

    /**
     * @return list<array{class-string<Seq>, class-string<NonEmptySeq>}>
     */
    public function seqWithNonEmptySeqClassDataProvider(): array
    {
        return [
            [ArrayList::class, NonEmptyArrayList::class],
            [LinkedList::class, NonEmptyLinkedList::class],
        ];
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testAppendAndPrepend(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([-2, -1, 0, 1, 2, 3, 4, 5, 6]),
            $seq::collect([1, 2, 3])
                ->prepended(0)
                ->appended(4)
                ->appendedAll([5, 6])
                ->prependedAll([-2, -1]),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testAt(string $seq): void
    {
        $collection = $seq::collect([0, 1, 2, 3, 4, 5]);

        $this->assertEquals(Option::some(0), $collection->at(0));
        $this->assertEquals(Option::some(3), $collection->at(3));
        $this->assertEquals(Option::some(5), $collection->at(5));
        $this->assertEquals(Option::none(), $collection->at(6));
        $this->assertEquals(Option::some(0), $collection(0));
        $this->assertEquals(Option::some(3), $collection(3));
        $this->assertEquals(Option::some(5), $collection(5));
        $this->assertEquals(Option::none(), $collection(6));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testEvery(string $seq): void
    {
        $collection = $seq::collect([0, 1, 2, 3, 4, 5]);

        $this->assertTrue($collection->every(fn($i) => $i >= 0));
        $this->assertFalse($collection->every(fn($i) => $i > 0));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testExists(string $seq): void
    {
        /** @var Seq<int|Foo> $collection */
        $collection = $seq::collect([new Foo(1), 1, new Foo(1)]);

        $this->assertTrue($collection->exists(fn($i) => $i === 1));
        $this->assertFalse($collection->exists(fn($i) => $i === 2));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTraverseOption(string $seq): void
    {
        /** @var Seq<int> $seq1 */
        $seq1 = $seq::collect([1, 2, 3]);

        $this->assertEquals(
            Option::some($seq1),
            $seq1->traverseOption(fn($x) => $x >= 1 ? Option::some($x) : Option::none()),
        );
        $this->assertEquals(
            Option::some($seq1),
            $seq1->map(fn($x) => $x >= 1 ? Option::some($x) : Option::none())->sequenceOption(),
        );

        /** @var Seq<int> $seq2 */
        $seq2 = $seq::collect([0, 1, 2]);

        $this->assertEquals(
            Option::none(),
            $seq2->traverseOption(fn($x) => $x >= 1 ? Option::some($x) : Option::none()),
        );
        $this->assertEquals(
            Option::none(),
            $seq2->map(fn($x) => $x >= 1 ? Option::some($x) : Option::none())->sequenceOption(),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTraverseEither(string $seq): void
    {
        /** @var Seq<int> $seq1 */
        $seq1 = $seq::collect([1, 2, 3]);

        $this->assertEquals(
            Either::right($seq1),
            $seq1->traverseEither(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::right($seq1),
            $seq1->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEither(),
        );

        /** @var Seq<int> $seq2 */
        $seq2 = $seq::collect([0, 1, 2]);

        $this->assertEquals(
            Either::left('err'),
            $seq2->traverseEither(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::left('err'),
            $seq2->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEither(),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTraverseEitherMerged(string $seq): void
    {
        /** @var Seq<int> $seq1 */
        $seq1 = $seq::collect([1, 2, 3]);

        $this->assertEquals(
            Either::right($seq1),
            $seq1->traverseEitherMerged(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::right($seq1),
            $seq1->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEitherMerged(),
        );

        /** @var Seq<int> $seq2 */
        $seq2 = $seq::collect([-2, -1, 0, 1, 2]);

        $this->assertEquals(
            Either::left(['wrong: -2', 'wrong: -1', 'wrong: 0']),
            $seq2->traverseEitherMerged(fn($x) => $x >= 1 ? Either::right($x) : Either::left(["wrong: {$x}"])),
        );
        $this->assertEquals(
            Either::left(['wrong: -2', 'wrong: -1', 'wrong: 0']),
            $seq2->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left(["wrong: {$x}"]))->sequenceEitherMerged(),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testPartition(string $seq): void
    {
        $actual = $seq::collect([0, 1, 2, 3, 4, 5])->partition(fn($i) => $i < 3);

        $expected = Separated::create(
            $seq::collect([3, 4, 5]),
            $seq::collect([0, 1, 2]),
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testPartitionMap(string $seq): void
    {
        $this->assertEquals(
            Separated::create(
                $seq::collect(['L: 5']),
                $seq::collect(['R: 0', 'R: 1', 'R: 2', 'R: 3', 'R: 4']),
            ),
            $seq::collect([0, 1, 2, 3, 4, 5])->partitionMap(fn($i) => $i >= 5
                ? Either::left("L: {$i}")
                : Either::right("R: {$i}")),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFilter(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([1]),
            $seq::collect([new Foo(1), 1, new Foo(1)])->filter(fn($i) => $i === 1),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFilterMap(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([1, 2]),
            $seq::collect(['zero', '1', '2'])->filterMap(fn($e) => Option::when(is_numeric($e), fn() => (int) $e)),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFilterNotNull(string $seq): void
    {
        $this->assertEquals($seq::collect([1, 3]), $seq::collect([1, null, 3])->filterNotNull());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFirst(string $seq): void
    {
        /** @var Seq<Foo|int> */
        $collection = $seq::collect([new Foo(1), 2, 1, 3]);

        $this->assertEquals(Option::some(1), $collection->first(fn($e) => 1 === $e));
        $this->assertEquals(Option::none(), $collection->first(fn($e) => 5 === $e));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFlatMap(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([1, 2, 3, 4, 5, 6]),
            $seq::collect([2, 5])->flatMap(fn($e) => [$e - 1, $e, $e + 1]),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFlatten(string $seq): void
    {
        $this->assertEquals($seq::empty(), $seq::collect([])->flatten());
        $this->assertEquals($seq::collect([1, 2, 3, 4, 5, 6]), $seq::collect([
            ArrayList::collect([1, 2]),
            ArrayList::collect([3, 4]),
            ArrayList::collect([5, 6]),
        ])->flatten());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testHead(string $seq): void
    {
        $this->assertEquals(Option::some(2), $seq::collect([2, 5])->head());
        $this->assertEquals(Option::none(), $seq::collect([])->head());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testLast(string $seq): void
    {
        $this->assertEquals(Option::some(3), $seq::collect([2, 3, 0])->last(fn($e) => $e > 0));
        $this->assertEquals(Option::none(), $seq::collect([])->last(fn($e) => $e > 0));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFirstAndLastElement(string $seq): void
    {
        $this->assertEquals(Option::some(1), $seq::collect([1, 2, 3])->firstElement());
        $this->assertEquals(Option::some(3), $seq::collect([1, 2, 3])->lastElement());
        $this->assertEquals(Option::none(), $seq::collect([])->firstElement());
        $this->assertEquals(Option::none(), $seq::collect([])->lastElement());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testMap(string $seq): void
    {
        $this->assertEquals(
            $seq::collect(['2', '3', '4']),
            $seq::collect([1, 2, 3])->map(fn($e) => (string) ($e + 1)),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testMapN(string $seq): void
    {
        $tuples = [
            [1, true, true],
            [2, true, false],
            [3, false, false],
        ];
        $expected = [
            new Foo(a: 1, b: true, c: true),
            new Foo(a: 2, b: true, c: false),
            new Foo(a: 3, b: false, c: false),
        ];

        $this->assertEquals(
            $seq::collect($expected),
            $seq::collect($tuples)->mapN(Foo::create(...)),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFold(string $seq): void
    {
        $this->assertEquals(
            '123',
            $seq::collect(['1', '2', '3'])->fold('')(fn($acc, $e) => $acc . $e)
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testReverse(string $seq): void
    {
        $this->assertEquals(
            $seq::collect(['3', '2', '1']),
            $seq::collect(['1', '2', '3'])->reverse(),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTail(string $seq): void
    {
        $this->assertEquals($seq::collect(['2', '3']), $seq::collect(['1', '2', '3'])->tail());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testInit(string $seq): void
    {
        $this->assertEquals($seq::collect(['1', '2']), $seq::collect(['1', '2', '3'])->init());
    }

    /**
     * @param class-string<Seq> $seq
     * @param class-string<NonEmptySeq> $nonEmptySeq
     * @dataProvider seqWithNonEmptySeqClassDataProvider
     */
    public function testGroupMap(string $seq, string $nonEmptySeq): void
    {
        $foo1 = new Foo(1);
        $foo2 = new Foo(2);
        $foo3 = new Foo(1);
        $foo4 = new Foo(3);

        $this->assertEquals(
            HashMap::collectPairs([
                [$foo1, $nonEmptySeq::collectNonEmpty(['2', '2'])],
                [$foo2, $nonEmptySeq::collectNonEmpty(['3'])],
                [$foo4, $nonEmptySeq::collectNonEmpty(['4'])],
            ]),
            $seq::collect([$foo1, $foo2, $foo3, $foo4])->groupMap(
                fn(Foo $v) => $v,
                fn(Foo $v) => (string)($v->a + 1),
            ),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @param class-string<NonEmptySeq> $nonEmptySeq
     * @dataProvider seqWithNonEmptySeqClassDataProvider
     */
    public function testGroupBy(string $seq, string $nonEmptySeq): void
    {
        $collection = $seq::collect([
            $v1 = new Foo(a: 100),
            $v2 = new Foo(a: 100),
            $v3 = new Foo(a: 200),
            $v4 = new Foo(a: 300),
            $v5 = new Foo(a: 300),
        ]);

        $this->assertEquals(
            HashMap::collect([
                100 => $nonEmptySeq::collectNonEmpty([$v1, $v2]),
                200 => $nonEmptySeq::collectNonEmpty([$v3]),
                300 => $nonEmptySeq::collectNonEmpty([$v4, $v5]),
            ]),
            $collection->groupBy(fn(Foo $v) => $v->a),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testGroupMapReduce(string $seq): void
    {
        $this->assertEquals(
            HashMap::collect([
                10 => [10, 15, 20],
                20 => [10, 15],
                30 => [20],
            ]),
            $seq::collect([
                ['id' => 10, 'sum' => 10],
                ['id' => 10, 'sum' => 15],
                ['id' => 10, 'sum' => 20],
                ['id' => 20, 'sum' => 10],
                ['id' => 20, 'sum' => 15],
                ['id' => 30, 'sum' => 20],
            ])->groupMapReduce(
                fn(array $a) => $a['id'],
                fn(array $a) => [$a['sum']],
                fn(array $old, array $new) => [...$old, ...$new],
            )
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTap(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([2, 3]),
            $seq::collect([new Foo(1), new Foo(2)])
                ->tap(fn(Foo $foo) => $foo->a = $foo->a + 1)
                ->map(fn(Foo $foo) => $foo->a),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testSorted(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([1, 2, 3]),
            $seq::collect([3, 2, 1])->sorted(),
        );

        $this->assertEquals(
            $seq::collect([3, 2, 1]),
            $seq::collect([1, 2, 3])->sortedDesc(),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testSortedByComparator(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([1, 2, 3]),
            $seq::collect([3, 2, 1])->sorted(fn($l, $r) => $l <=> $r),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testSortedBy(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([new Foo(1), new Foo(2), new Foo(3)]),
            $seq::collect([new Foo(3), new Foo(2), new Foo(1)])->sortedBy(fn(Foo $obj) => $obj->a),
        );

        $this->assertEquals(
            $seq::collect([new Foo(3), new Foo(2), new Foo(1)]),
            $seq::collect([new Foo(1), new Foo(2), new Foo(3)])->sortedDescBy(fn(Foo $obj) => $obj->a),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testIsEmpty(string $seq): void
    {
        $this->assertFalse($seq::collect([1, 2, 3])->isEmpty());
        $this->assertTrue($seq::collect([])->isEmpty());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTakeAndDrop(string $seq): void
    {
        $collection = $seq::collect([0, 1, 2]);
        $this->assertEquals($seq::collect([0, 1]), $collection->takeWhile(fn($e) => $e < 2));
        $this->assertEquals($seq::collect([2]), $collection->dropWhile(fn($e) => $e < 2));
        $this->assertEquals($seq::collect([0, 1]), $collection->take(2));
        $this->assertEquals($seq::collect([2]), $collection->drop(2));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testIntersperse(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([0 , ',', 1, ',', 2]),
            $seq::collect([0, 1, 2])->intersperse(','),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testZip(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([[0, 'a'], [1, 'b']]),
            $seq::collect([0, 1, 2])->zip(['a', 'b']),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testZipWithKeys(string $seq): void
    {
        $this->assertEquals(
            $seq::collect([[0, 1], [1, 2], [2, 3]]),
            $seq::collect([1, 2, 3])->zipWithKeys(),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testMkString(string $seq): void
    {
        $this->assertEquals('(0,1,2)', $seq::collect([0, 1, 2])->mkString('(', ',', ')'));
        $this->assertEquals('()', $seq::empty()->mkString('(', ',', ')'));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testReindex(string $seq): void
    {
        $this->assertEquals(
            HashMap::collect([
                'key-1' => 1,
                'key-2' => 2,
                'key-3' => 3,
            ]),
            $seq::collect([1, 2, 3])->reindex(fn($value) => "key-{$value}"),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testMax(string $seq): void
    {
        $this->assertEquals(Option::none(), $seq::collect([])->max());
        $this->assertEquals(Option::some(7), $seq::collect([3, 7, 2])->max());
        $this->assertEquals(Option::some(9), $seq::collect([9, 1, 2])->max());

        /** @var Seq<Foo> $empty */
        $empty = $seq::collect([]);
        $this->assertEquals(Option::none(), $empty->maxBy(fn(Foo $f) => $f->a));
        $this->assertEquals(Option::some(new Foo(a: 7)), $seq::collect([new Foo(a: 3), new Foo(a: 7), new Foo(a: 2)])->maxBy(fn(Foo $f) => $f->a));
        $this->assertEquals(Option::some(new Foo(a: 9)), $seq::collect([new Foo(a: 9), new Foo(a: 1), new Foo(a: 2)])->maxBy(fn(Foo $f) => $f->a));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testMin(string $seq): void
    {
        $this->assertEquals(Option::none(), $seq::collect([])->min());
        $this->assertEquals(Option::some(2), $seq::collect([3, 7, 2])->min());
        $this->assertEquals(Option::some(1), $seq::collect([9, 1, 2])->min());

        /** @var Seq<Foo> $empty */
        $empty = $seq::collect([]);
        $this->assertEquals(Option::none(), $empty->minBy(fn(Foo $f) => $f->a));
        $this->assertEquals(Option::some(new Foo(a: 2)), $seq::collect([new Foo(a: 3), new Foo(a: 7), new Foo(a: 2)])->minBy(fn(Foo $f) => $f->a));
        $this->assertEquals(Option::some(new Foo(a: 1)), $seq::collect([new Foo(a: 9), new Foo(a: 1), new Foo(a: 2)])->minBy(fn(Foo $f) => $f->a));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testUniqBy(string $seq): void
    {
        $expected = $seq::collect([['n' => 1], ['n' => 2]]);
        $actual = $seq::collect([['n' => 1], ['n' => 1], ['n' => 2]])->uniqueBy(fn(array $x) => $x['n']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTapN(string $seq): void
    {
        $collection = $seq::collect([
            [new Foo(a: 1, b: true, c: false), 2, false, true],
            [new Foo(a: 2, b: false, c: true), 1, true, false],
        ]);

        $expected = $seq::collect([
            new Foo(a: 2, b: false, c: true),
            new Foo(a: 1, b: true, c: false),
        ]);

        $actual = $collection
            ->tapN(function(Foo $value, int $a, bool $b, bool $c): void {
                $value->a = $a;
                $value->b = $b;
                $value->c = $c;
            })
            ->mapN(fn(Foo $value) => $value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testEveryN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $this->assertTrue($collection->everyN(fn(int $a, int $b) => ($a + $b) <= 6));
        $this->assertFalse($collection->everyN(fn(int $a, int $b) => ($a + $b) < 6));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testExistsN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $this->assertTrue($collection->existsN(fn(int $a, int $b) => ($a + $b) === 6));
        $this->assertFalse($collection->existsN(fn(int $a, int $b) => ($a + $b) === 7));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFlatMapN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $expected = $seq::collect([1, 1, 2, 2, 3, 3]);
        $actual = $collection->flatMapN(fn(int $a, int $b) => [$a, $b]);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFilterMapN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $expected = $seq::collect([3]);
        $actual = $collection->filterMapN(fn(int $a, int $b) => Option::when($a + $b >= 6, fn() => $a));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFilterN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $expected = $seq::collect([[3, 3]]);
        $actual = $collection->filterN(fn(int $a, int $b) => $a + $b >= 6);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testReindexN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $expected = HashMap::collect([
            2 => [1, 1],
            4 => [2, 2],
            6 => [3, 3],
        ]);
        $actual = $collection->reindexN(fn(int $a, int $b) => $a + $b);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testLastN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1, 'fst'],
            [1, 1, 'lst'],
            [2, 2, 'fst'],
            [2, 2, 'lst'],
            [3, 3, 'fst'],
            [3, 3, 'lst'],
        ]);

        $this->assertEquals(Option::some([1, 1, 'lst']), $collection->lastN(fn(int $a, int $b) => $a + $b === 2));
        $this->assertEquals(Option::some([2, 2, 'lst']), $collection->lastN(fn(int $a, int $b) => $a + $b === 4));
        $this->assertEquals(Option::some([3, 3, 'lst']), $collection->lastN(fn(int $a, int $b) => $a + $b === 6));
        $this->assertEquals(Option::none(), $collection->lastN(fn(int $a, int $b) => $a + $b === 9));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFirstN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1, 'fst'],
            [1, 1, 'lst'],
            [2, 2, 'fst'],
            [2, 2, 'lst'],
            [3, 3, 'fst'],
            [3, 3, 'lst'],
        ]);

        $this->assertEquals(Option::some([1, 1, 'fst']), $collection->firstN(fn(int $a, int $b) => $a + $b === 2));
        $this->assertEquals(Option::some([2, 2, 'fst']), $collection->firstN(fn(int $a, int $b) => $a + $b === 4));
        $this->assertEquals(Option::some([3, 3, 'fst']), $collection->firstN(fn(int $a, int $b) => $a + $b === 6));
        $this->assertEquals(Option::none(), $collection->firstN(fn(int $a, int $b) => $a + $b === 9));
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testPartitionN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1, 'lhs'],
            [1, 1, 'lhs'],
            [1, 2, 'lhs'],
            [1, 2, 'lhs'],
            [2, 2, 'rhs'],
            [2, 2, 'rhs'],
            [3, 3, 'rhs'],
            [3, 3, 'rhs'],
        ]);

        $expected = Separated::create(
            left: $seq::collect([
                [1, 1, 'lhs'],
                [1, 1, 'lhs'],
                [1, 2, 'lhs'],
                [1, 2, 'lhs'],
            ]),
            right: $seq::collect([
                [2, 2, 'rhs'],
                [2, 2, 'rhs'],
                [3, 3, 'rhs'],
                [3, 3, 'rhs'],
            ]),
        );
        $actual = $collection->partitionN(fn(int $a, int $b) => ($a + $b) >= 4);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testPartitionMapN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1, 'lhs'],
            [1, 1, 'lhs'],
            [1, 2, 'lhs'],
            [1, 2, 'lhs'],
            [2, 2, 'rhs'],
            [2, 2, 'rhs'],
            [3, 3, 'rhs'],
            [3, 3, 'rhs'],
        ]);

        $expected = Separated::create(
            left: $seq::collect([
                [1, 1, 'lhs'],
                [1, 1, 'lhs'],
                [1, 2, 'lhs'],
                [1, 2, 'lhs'],
            ]),
            right: $seq::collect([
                [2, 2, 'rhs'],
                [2, 2, 'rhs'],
                [3, 3, 'rhs'],
                [3, 3, 'rhs'],
            ]),
        );
        $actual = $collection->partitionMapN(fn(int $a, int $b, string $mark) => Either::when(
            cond: ($a + $b) >= 4,
            right: fn() => [$a, $b, $mark],
            left: fn() => [$a, $b, $mark],
        ));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTraverseEitherN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $this->assertEquals(
            Either::right($seq::collect([2, 4, 6])),
            $collection->traverseEitherN(
                fn(int $a, int $b) => $a + $b <= 6 ? Either::right($a + $b) : Either::left('invalid'),
            ),
        );
        $this->assertEquals(
            Either::left('invalid'),
            $collection->traverseEitherN(
                fn(int $a, int $b) => $a + $b < 6 ? Either::right($a + $b) : Either::left('invalid'),
            ),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTraverseEitherMergedN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 4],
        ]);

        $this->assertEquals(
            Either::right($seq::collect([2, 4, 6, 8])),
            $collection->traverseEitherMergedN(
                fn(int $a, int $b) => $a + $b <= 8 ? Either::right($a + $b) : Either::left(['invalid']),
            ),
        );
        $this->assertEquals(
            Either::left(['invalid: 3 + 3', 'invalid: 4 + 4']),
            $collection->traverseEitherMergedN(
                fn(int $a, int $b) => $a + $b < 6 ? Either::right($a + $b) : Either::left(["invalid: {$a} + {$b}"]),
            ),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testTraverseOptionN(string $seq): void
    {
        $collection = $seq::collect([
            [1, 1],
            [2, 2],
            [3, 3],
        ]);

        $this->assertEquals(
            Option::some($seq::collect([2, 4, 6])),
            $collection->traverseOptionN(
                fn(int $a, int $b) => $a + $b <= 6 ? Option::some($a + $b) : Option::none(),
            ),
        );
        $this->assertEquals(
            Option::none(),
            $collection->traverseOptionN(
                fn(int $a, int $b) => $a + $b < 6 ? Option::some($a + $b) : Option::none(),
            ),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testCount(string $seq): void
    {
        $this->assertEquals(3, $seq::collect([1, 2, 3])->count());
        $this->assertEquals(0, $seq::collect([])->count());
        $this->assertEquals(2, $seq::collect([2, 3])->count());
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testIterator(string $seq): void
    {
        $agg = [];

        foreach ($seq::collect([1, 2, 3]) as $num) {
            $agg[] = $num + 1;
        }

        $this->assertEquals([2, 3, 4], $agg);
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testFirstMap(string $seq): void
    {
        $this->assertEquals(
            Option::none(),
            $seq::collect(['fst', 'snd', 'thr'])->firstMap(fn($i) => Option::when(is_numeric($i), fn() => (int) $i)),
        );

        $this->assertEquals(
            Option::some(1),
            $seq::collect(['zero', '1', '2'])->firstMap(fn($i) => Option::when(is_numeric($i), fn() => (int) $i)),
        );
    }

    /**
     * @param class-string<Seq> $seq
     * @dataProvider seqClassDataProvider
     */
    public function testLastMap(string $seq): void
    {
        $this->assertEquals(
            Option::none(),
            $seq::collect(['fst', 'snd', 'thr'])->lastMap(fn($i) => Option::when(is_numeric($i), fn() => (int) $i)),
        );

        $this->assertEquals(
            Option::some(2),
            $seq::collect(['zero', '1', '2'])->lastMap(fn($i) => Option::when(is_numeric($i), fn() => (int) $i)),
        );
    }
}
