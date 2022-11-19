<?php

declare(strict_types=1);

namespace Tests\Static\Functions\Collection;

use function Fp\Collection\flatMap;

final class FlatMapStaticTest
{
    /**
     * @param array<string, int> $coll
     * @return list<int>
     */
    public function testWithArray(array $coll): array
    {
        return flatMap(
            $coll,
            fn(int $v) => [$v - 1, $v + 1]
        );
    }
}
