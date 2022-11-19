<?php

declare(strict_types=1);

namespace Tests\Runtime\Interfaces\Map;

use Fp\Collections\HashMap;
use PHPUnit\Framework\TestCase;

final class MapCollectorTest extends TestCase
{
    public function testCollect(): void
    {
        $this->assertEquals(
            [],
            HashMap::empty()->toList(),
        );

        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collectPairs([['a', 1], ['b', 2]])->toList(),
        );

        $this->assertEquals(
            [['a', 1], ['b', 2]],
            HashMap::collect(['a' => 1, 'b' => 2])->toList(),
        );
    }
}
