<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Seq;

use Fp\Collections\ArrayList;
use Fp\Collections\LinkedList;
use Fp\Collections\Seq;
use Generator;
use PHPUnit\Framework\TestCase;

final class SeqTest extends TestCase
{
    public function provideTestCastsData(): Generator
    {
        yield ArrayList::class => [ArrayList::collect([1, 2, 3])];
        yield LinkedList::class => [LinkedList::collect([1, 2, 3])];
    }

    /**
     * @dataProvider provideTestCastsData
     */
    public function testCasts(Seq $seq): void
    {
        $this->assertEquals([1, 2, 3], $seq->toArray());
        $this->assertEquals([1, 2, 3], $seq->toLinkedList()->toArray());
        $this->assertEquals([1, 2, 3], $seq->toLinkedList()->toArray());
        $this->assertEquals([1, 2, 3], $seq->toArrayList()->toArray());
        $this->assertEquals([1, 2, 3], $seq->toArrayList()->toArray());
        $this->assertEquals([1, 2, 3], $seq->toHashSet()->toArray());
        $this->assertEquals([[1, 1], [2, 2], [3, 3]], $seq->toHashMap(fn($e) => [$e, $e])->toArray());
    }

    /**
     * @dataProvider provideTestCastsData
     */
    public function testCount(Seq $seq): void
    {
        $this->assertEquals(3, $seq->count());
        $this->assertEquals(3, $seq->count());
    }
}
