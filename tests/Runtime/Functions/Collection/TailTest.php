<?php

declare(strict_types=1);

namespace Tests\Runtime\Functions\Collection;

use PHPUnit\Framework\TestCase;

use function Fp\Collection\tail;

final class TailTest extends TestCase
{
    public function testTail(): void
    {
        $this->assertEquals([2, 3, 4], tail([1, 2, 3, 4]));
    }
}
