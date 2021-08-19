<?php

declare(strict_types=1);

namespace Tests\Runtime\Classes\HashSet;

use Fp\Collections\HashSet;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Foo;

final class HashSetOpsTest extends TestCase
{
    public function testContains(): void
    {
        /** @var HashSet<int> $hs */
        $hs = HashSet::collect([1, 2, 2]);

        $this->assertTrue($hs->contains(1));
        $this->assertTrue($hs->contains(2));
        $this->assertFalse($hs->contains(3));

        $this->assertTrue($hs(1));
        $this->assertTrue($hs(2));
        $this->assertFalse($hs(3));
    }

    public function testUpdatedAndRemoved(): void
    {
        /** @var HashSet<int> $hs */
        $hs = HashSet::collect([1, 2, 2])->updated(3)->removed(1);

        $this->assertEquals([2, 3], $hs->toArray());
    }

    public function testEvery(): void
    {
        $hs = HashSet::collect([0, 1, 2, 3, 4, 5]);

        $this->assertTrue($hs->every(fn($i) => $i >= 0));
        $this->assertFalse($hs->every(fn($i) => $i > 0));
    }

    public function testExists(): void
    {
        /** @var HashSet<object|scalar> $hs */
        $hs = HashSet::collect([new Foo(1), 1, 1, new Foo(1)]);

        $this->assertTrue($hs->exists(fn($i) => $i === 1));
        $this->assertFalse($hs->exists(fn($i) => $i === 2));
    }

    public function testFilter(): void
    {
        $hs = HashSet::collect([new Foo(1), 1, 1, new Foo(1)]);
        $this->assertEquals([1], $hs->filter(fn($i) => $i === 1)->toArray());
    }

    public function testFlatMap(): void
    {
        $this->assertEquals(
            [1, 2, 3, 4, 5, 6],
            HashSet::collect([2, 5])->flatMap(fn($e) => [$e - 1, $e, $e + 1])->toArray()
        );
    }

    public function testFold(): void
    {
        /** @psalm-var HashSet<int> $list */
        $list = HashSet::collect([2, 3]);

        $this->assertEquals(
            6,
            $list->fold(1, fn(int $acc, $e) => $acc + $e)
        );
    }

    public function testReduce(): void
    {
        /** @var HashSet<string> $list */
        $list = HashSet::collect(['1', '2', '3']);

        $this->assertEquals(
            '123',
            $list->reduce(fn($acc, $e) => $acc . $e)->get()
        );
    }

    public function testMap(): void
    {
        $this->assertEquals(
            ['2', '3', '4'],
            HashSet::collect([1, 2, 2, 3])->map(fn($e) => (string) ($e + 1))->toArray()
        );
    }
}
