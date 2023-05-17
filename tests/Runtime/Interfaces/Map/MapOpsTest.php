<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Map;

use Fp\Collections\HashMap;
use Fp\Collections\Map;
use Fp\Collections\NonEmptyHashMap;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Functional\Separated\Separated;
use Generator;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Foo;

final class MapOpsTest extends TestCase
{
    public function testGet(): void
    {
        $hm = HashMap::collect(['a' => 1, 'b' => 2]);

        $this->assertEquals(2, $hm->get('b')->get());
        $this->assertEquals(2, $hm('b')->get());
    }

    public function testUpdatedAndRemoved(): void
    {
        $hm = HashMap::collect(['a' => 1, 'b' => 2]);
        $hm = $hm->appended('c', 3);
        $hm = $hm->removed('a');

        $this->assertEquals([['b', 2], ['c', 3]], $hm->toList());
    }

    public function testEvery(): void
    {
        $hm = HashMap::collect(['a' => 0, 'b' => 1]);

        $this->assertTrue($hm->every(fn($entry) => $entry >= 0));
        $this->assertFalse($hm->every(fn($entry) => $entry > 0));
    }

    public function testEveryN(): void
    {
        $this->assertTrue(
            HashMap
                ::collect([
                    'fst' => [1, 1],
                    'snd' => [2, 2],
                    'thr' => [3, 3],
                ])
                ->everyN(fn(int $a, int $b) => ($a + $b) <= 6),
        );

        $this->assertFalse(
            HashMap
                ::collect([
                    'fst' => [1, 1],
                    'snd' => [2, 2],
                    'thr' => [3, 3],
                ])
                ->everyN(fn(int $a, int $b) => ($a + $b) < 6),
        );
    }

    public function testExists(): void
    {
        $hm = HashMap::collect(['a' => 0, 'b' => 1]);

        $this->assertTrue($hm->exists(fn($entry) => $entry > 0));
        $this->assertFalse($hm->exists(fn($entry) => $entry > 1));
    }

    public function testExistsN(): void
    {
        $this->assertTrue(
            HashMap
                ::collect([
                    'fst' => [1, 1],
                    'snd' => [2, 2],
                    'thr' => [3, 3],
                ])
                ->existsN(fn(int $a, int $b) => ($a + $b) === 6),
        );
        $this->assertFalse(
            HashMap
                ::collect([
                    'fst' => [1, 1],
                    'snd' => [2, 2],
                    'thr' => [3, 3],
                ])
                ->existsN(fn(int $a, int $b) => ($a + $b) === 7),
        );
    }

    public function provideTestTraverseData(): Generator
    {
        yield HashMap::class => [
            HashMap::collect(['fst' => 1, 'snd' => 2, 'thr' => 3]),
            HashMap::collect(['zro' => 0, 'fst' => 1, 'thr' => 2]),
        ];
    }

    /**
     * @param Map<string, int> $map1
     * @param Map<string, int> $map2
     *
     * @dataProvider provideTestTraverseData
     */
    public function testTraverseOption(Map $map1, Map $map2): void
    {
        $this->assertEquals(
            Option::some($map1),
            $map1->traverseOption(fn($x) => $x >= 1 ? Option::some($x) : Option::none()),
        );
        $this->assertEquals(
            Option::none(),
            $map2->traverseOption(fn($x) => $x >= 1 ? Option::some($x) : Option::none()),
        );
        $this->assertEquals(
            Option::some($map1),
            $map1->map(fn($x) => $x >= 1 ? Option::some($x) : Option::none())->sequenceOption(),
        );
        $this->assertEquals(
            Option::none(),
            $map2->map(fn($x) => $x >= 1 ? Option::some($x) : Option::none())->sequenceOption(),
        );
    }

    public function testTraverseOptionN(): void
    {
        $collection = HashMap::collect([
            'fst' => [1, 1],
            'snd' => [2, 2],
            'thr' => [3, 3],
        ]);

        $this->assertEquals(
            Option::some(HashMap::collect(['fst' => 2, 'snd' => 4, 'thr' => 6])),
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
     * @param Map<string, int> $map1
     * @param Map<string, int> $map2
     *
     * @dataProvider provideTestTraverseData
     */
    public function testTraverseEither(Map $map1, Map $map2): void
    {
        $this->assertEquals(
            Either::right($map1),
            $map1->traverseEither(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::left('err'),
            $map2->traverseEither(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err')),
        );
        $this->assertEquals(
            Either::right($map1),
            $map1->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEither(),
        );
        $this->assertEquals(
            Either::left('err'),
            $map2->map(fn($x) => $x >= 1 ? Either::right($x) : Either::left('err'))->sequenceEither(),
        );
    }

    public function testTraverseEitherN(): void
    {
        $collection = HashMap::collect([
            'fst' => [1, 1],
            'snd' => [2, 2],
            'thr' => [3, 3],
        ]);

        $this->assertEquals(
            Either::right(HashMap::collect(['fst' => 2, 'snd' => 4, 'thr' => 6])),
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

    public function testPartition(): void
    {
        $expected = Separated::create(
            HashMap::collect(['k3' => 3, 'k4' => 4, 'k5' => 5]),
            HashMap::collect(['k0' => 0, 'k1' => 1, 'k2' => 2]),
        );

        $actual = HashMap::collect(['k0' => 0, 'k1' => 1, 'k2' => 2, 'k3' => 3, 'k4' => 4, 'k5' => 5])
            ->partition(fn($i) => $i < 3);

        $this->assertEquals($expected, $actual);
    }

    public function testPartitionN(): void
    {
        $collection = HashMap::collect([
            'k1' => [1, 1, 'lhs'],
            'k2' => [1, 1, 'lhs'],
            'k3' => [1, 2, 'lhs'],
            'k4' => [1, 2, 'lhs'],
            'k5' => [2, 2, 'rhs'],
            'k6' => [2, 2, 'rhs'],
            'k7' => [3, 3, 'rhs'],
            'k8' => [3, 3, 'rhs'],
        ]);

        $expected = Separated::create(
            left: HashMap::collect([
                'k1' => [1, 1, 'lhs'],
                'k2' => [1, 1, 'lhs'],
                'k3' => [1, 2, 'lhs'],
                'k4' => [1, 2, 'lhs'],
            ]),
            right: HashMap::collect([
                'k5' => [2, 2, 'rhs'],
                'k6' => [2, 2, 'rhs'],
                'k7' => [3, 3, 'rhs'],
                'k8' => [3, 3, 'rhs'],
            ]),
        );
        $actual = $collection->partitionN(fn(int $a, int $b) => ($a + $b) >= 4);

        $this->assertEquals($expected, $actual);
    }

    public function testPartitionMap(): void
    {
        $expected = Separated::create(
            HashMap::collect(['k5' => 'L:5']),
            HashMap::collect(['k0' => 'R:0', 'k1' => 'R:1', 'k2' => 'R:2', 'k3' => 'R:3', 'k4' => 'R:4']),
        );

        $actual = HashMap::collect(['k0' => 0, 'k1' => 1, 'k2' => 2, 'k3' => 3, 'k4' => 4, 'k5' => 5])
            ->partitionMap(fn($i) => $i >= 5 ? Either::left("L:{$i}") : Either::right("R:{$i}"));

        $this->assertEquals($expected, $actual);
    }

    public function testPartitionMapN(): void
    {
        $collection = HashMap::collect([
            'k1' => [1, 1, 'lhs'],
            'k2' => [1, 1, 'lhs'],
            'k3' => [1, 2, 'lhs'],
            'k4' => [1, 2, 'lhs'],
            'k5' => [2, 2, 'rhs'],
            'k6' => [2, 2, 'rhs'],
            'k7' => [3, 3, 'rhs'],
            'k8' => [3, 3, 'rhs'],
        ]);

        $expected = Separated::create(
            left: HashMap::collect([
                'k1' => [1, 1, 'lhs'],
                'k2' => [1, 1, 'lhs'],
                'k3' => [1, 2, 'lhs'],
                'k4' => [1, 2, 'lhs'],
            ]),
            right: HashMap::collect([
                'k5' => [2, 2, 'rhs'],
                'k6' => [2, 2, 'rhs'],
                'k7' => [3, 3, 'rhs'],
                'k8' => [3, 3, 'rhs'],
            ]),
        );
        $actual = $collection->partitionMapN(fn(int $a, int $b, string $mark) => Either::when(
            cond: ($a + $b) >= 4,
            right: fn() => [$a, $b, $mark],
            left: fn() => [$a, $b, $mark],
        ));

        $this->assertEquals($expected, $actual);
    }

    public function testFilter(): void
    {
        $hm = HashMap::collect(['a' => new Foo(1), 'b' => 1, 'c' => new Foo(2)]);
        $this->assertEquals([['b', 1]], $hm->filter(fn($e) => $e === 1)->toList());
    }

    public function testFilterN(): void
    {
        $actual = HashMap
            ::collect([
                'fst' => [1, 1],
                'snd' => [2, 2],
                'thr' => [3, 3],
            ])
            ->filterN(fn(int $a, int $b) => $a + $b >= 6);

        $this->assertEquals(HashMap::collect(['thr' => [3, 3]]), $actual);
    }

    public function testFilterMap(): void
    {
        $this->assertEquals(
            [['b', 1], ['c', 2]],
            HashMap::collectPairs([['a', 'zero'], ['b', '1'], ['c', '2']])
                ->filterMap(fn($val) => is_numeric($val) ? Option::some((int) $val) : Option::none())
                ->toList()
        );
    }

    public function testFilterMapN(): void
    {
        $actual = HashMap
            ::collect([
                'fst' => [1, 1],
                'snd' => [2, 2],
                'thr' => [3, 3],
            ])
            ->filterMapN(fn(int $a, int $b) => Option::when($a + $b >= 6, fn() => $a));

        $this->assertEquals(HashMap::collect(['thr' => 3]), $actual);
    }

    public function testFlatten(): void
    {
        $this->assertEquals(
            HashMap::collect([]),
            HashMap::collect([
                HashMap::collect([]),
                HashMap::collect([]),
                HashMap::collect([]),
            ])->flatten(),
        );
        $this->assertEquals(
            HashMap::collect([
                'fst' => 1,
                'snd' => 2,
                'thr' => 3,
            ]),
            HashMap::collect([
                HashMap::collect(['fst' => 1]),
                HashMap::collect(['snd' => 2]),
                HashMap::collect(['thr' => 3]),
            ])->flatten(),
        );
    }

    public function testFlatMap(): void
    {
        $hm = HashMap::collectPairs([['2', 2], ['5', 5]]);

        $this->assertEquals(
            HashMap::collect([
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
            ]),
            $hm->flatMap(fn($val) => [
                ($val - 1) => $val - 1,
                ($val) => $val,
                ($val + 1) => $val + 1,
            ]),
        );

        $this->assertEquals(
            HashMap::collect([
                '2' => 20,
                '5' => 5,
            ]),
            $hm->flatMap(fn($val) => [
                '2' => 20,
                (string) $val => $val,
            ]),
        );
    }

    public function testFlatMapN(): void
    {
        $this->assertEquals(
            HashMap::collect([
                'k2' => 2,
                'k3' => 3,
                'k4' => 4,
                'k5' => 5,
                'k6' => 6,
                'k7' => 7,
            ]),
            HashMap
                ::collect([
                    'fst' => [1, 2],
                    'snd' => [3, 4],
                    'thr' => [5, 6],
                ])
                ->flatMapN(fn(int $a, int $b) => [
                    'k' . ($a + 1) => $a + 1,
                    'k' . ($b + 1) => $b + 1,
                ]),
        );
    }

    public function testFold(): void
    {
        $hm = HashMap::collectPairs([['2', 2], ['3', 3]]);

        $this->assertEquals(6, $hm->fold(1)(fn($acc, $cur) => $acc + $cur));
    }

    public function testMap(): void
    {
        $hm = HashMap::collectPairs([['2', 22], ['3', 33]]);

        $this->assertEquals(
            [['2', 'val-22'], ['3', 'val-33']],
            $hm->map(fn($e) => "val-{$e}")->toList()
        );

        $this->assertEquals(
            [['2', 'key-2-val-22'], ['3', 'key-3-val-33']],
            $hm->mapKV(fn($key, $elem) => "key-{$key}-val-{$elem}")->toList()
        );
    }

    public function testMapN(): void
    {
        $tuples = [
            'fst' => [1, true, true],
            'snd' => [2, true, false],
            'thr' => [3, false, false],
        ];

        $this->assertEquals(
            HashMap::collect([
                'fst' => new Foo(1, true, true),
                'snd' => new Foo(2, true, false),
                'thr' => new Foo(3, false, false),
            ]),
            HashMap::collect($tuples)->mapN(Foo::create(...)),
        );
    }

    public function testTap(): void
    {
        $hm = HashMap::collectPairs([['2', 22], ['3', 33]])
            ->tap(fn(int $v) => $v + 10);

        $this->assertEquals([['2', 22], ['3', 33]], $hm->toList());
    }

    public function testTapN(): void
    {
        $this->assertEquals(
            HashMap::collect(['snd' => 2, 'thr' => 3]),
            HashMap::collect(['snd' => [new Foo(1), 2], 'thr' => [new Foo(2), 3]])
                ->tapN(fn(Foo $foo, int $new) => $foo->a = $new)
                ->mapN(fn(Foo $foo) => $foo->a),
        );
    }

    public function testReindex(): void
    {
        $hm = HashMap::collectPairs([['2', 22], ['3', 33]]);

        $this->assertEquals(
            [[23, 22], [34, 33]],
            $hm->reindex(fn($v) => $v + 1)->toList()
        );

        $this->assertEquals(
            [['2-22', 22], ['3-33', 33]],
            $hm->reindexKV(fn($k, $v) => "{$k}-{$v}")->toList()
        );
    }

    public function testReindexN(): void
    {
        $this->assertEquals(
            HashMap::collectPairs([
                ['x-1', ['x', 1]],
                ['y-2', ['y', 2]],
                ['z-3', ['z', 3]],
            ]),
            HashMap
                ::collect([
                    'fst' => ['x', 1],
                    'snd' => ['y', 2],
                    'thr' => ['z', 3],
                ])
                ->reindexN(fn(string $a, int $b) => "{$a}-{$b}"),
        );
    }

    public function testGroupBy(): void
    {
        $this->assertEquals(
            HashMap::collect([
                'odd' => NonEmptyHashMap::collectNonEmpty(['fst' => 1, 'trd' => 3]),
                'even' => NonEmptyHashMap::collectNonEmpty(['snd' => 2]),
            ]),
            HashMap::collect(['fst' => 1, 'snd' => 2, 'trd' => 3])->groupBy(fn($i) => 0 === $i % 2 ? 'even' : 'odd'),
        );
    }

    /**
     * ```php
     * >>> HashMap::collect([
     * >>>     '10-1' => ['id' => 10, 'sum' => 10],
     * >>>     '10-2' => ['id' => 10, 'sum' => 15],
     * >>>     '10-3' => ['id' => 10, 'sum' => 20],
     * >>>     '20-1' => ['id' => 20, 'sum' => 10],
     * >>>     '20-2' => ['id' => 20, 'sum' => 15],
     * >>>     '30-1' => ['id' => 30, 'sum' => 20],
     * >>> ])->groupMap(
     * >>>     fn(array $a) => $a['id'],
     * >>>     fn(array $a) => $a['sum'] + 1,
     * >>> );
     * => HashMap(
     * =>   10 -> NonEmptyHashMap('10-3' => 21, '10-2' => 16, '10-1' => 11),
     * =>   20 -> NonEmptyHashMap('20-2' => 16, '20-1' => 11),
     * =>   30 -> NonEmptyHashMap('30-1' => 21),
     * => )
     * ```
     */
    public function testGroupMap(): void
    {
        $actual = HashMap::collect([
            '10-1' => ['id' => 10, 'sum' => 10],
            '10-2' => ['id' => 10, 'sum' => 15],
            '10-3' => ['id' => 10, 'sum' => 20],
            '20-1' => ['id' => 20, 'sum' => 10],
            '20-2' => ['id' => 20, 'sum' => 15],
            '30-1' => ['id' => 30, 'sum' => 20],
        ])->groupMap(
            fn(array $a) => $a['id'],
            fn(array $a) => $a['sum'] + 1,
        );

        $expected = HashMap::collect([
            10 => NonEmptyHashMap::collectNonEmpty([
                '10-3' => 21,
                '10-2' => 16,
                '10-1' => 11,
            ]),
            20 => NonEmptyHashMap::collectNonEmpty([
                '20-2' => 16,
                '20-1' => 11,
            ]),
            30 => NonEmptyHashMap::collectNonEmpty([
                '30-1' => 21,
            ]),
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testGroupMapReduce(): void
    {
        /** @var array<string, array{id: int, sum: int}> */
        $source = [
            '10-1' => ['id' => 10, 'sum' => 10],
            '10-2' => ['id' => 10, 'sum' => 15],
            '10-3' => ['id' => 10, 'sum' => 20],
            '20-1' => ['id' => 20, 'sum' => 10],
            '20-2' => ['id' => 20, 'sum' => 15],
            '30-1' => ['id' => 30, 'sum' => 20],
        ];
        $actual = HashMap::collect($source)->groupMapReduce(
            fn(array $a) => $a['id'],
            fn(array $a) => $a['sum'],
            fn(int $old, int $new) => $old + $new,
        );

        $expected = HashMap::collect([10 => 45, 20 => 25, 30 => 20]);

        $this->assertEquals($expected, $actual);
    }

    public function testKeys(): void
    {
        $hm = HashMap::collectPairs([['a', 22], ['b', 33]]);

        $this->assertEquals(
            ['a', 'b'],
            $hm->keys()->toList()
        );
    }

    public function testValues(): void
    {
        $hm = HashMap::collectPairs([['a', 22], ['b', 33]]);

        $this->assertEquals(
            [22, 33],
            $hm->values()->toList()
        );
    }

    public function testMerge(): void
    {
        $this->assertEquals(
            HashMap::collect(['a' => 1, 'b' => 2, 'c' => 3]),
            HashMap::collect(['a' => 1, 'b' => 3])->appendedAll(HashMap::collect(['b' => 2, 'c' => 3])),
        );
    }
}
