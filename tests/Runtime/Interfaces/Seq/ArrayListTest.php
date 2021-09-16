<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Seq;

use Fp\Collections\ArrayList;
use PHPUnit\Framework\TestCase;

final class ArrayListTest extends TestCase
{
    public function testCollect(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            ArrayList::collect([1, 2, 3])->toArray(),
        );
    }

    public function testCasts(): void
    {
        $this->assertEquals(
            [1, 2, 3],
            ArrayList::collect([1, 2, 3])->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            ArrayList::collect([1, 2, 3])->toLinkedList()->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            ArrayList::collect([1, 2, 3])->toArrayList()->toArray(),
        );

        $this->assertEquals(
            [1, 2, 3],
            ArrayList::collect([1, 2, 3])->toHashSet()->toArray(),
        );

        $this->assertEquals(
            [[1, 1], [2, 2], [3, 3]],
            ArrayList::collect([1, 2, 3])->toHashMap(fn($e) => [$e, $e])->toArray(),
        );
    }

    public function testCount(): void
    {
        $this->assertEquals(3, ArrayList::collect([1, 2, 3])->count());
    }
}
