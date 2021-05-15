<?php

declare(strict_types=1);

namespace Tests\Static\Functions\Collection;

use Fp\Functional\Option\Option;
use Fp\Functional\Tuple\Tuple2;
use Tests\PhpBlockTestCase;

final class PopTest extends PhpBlockTestCase
{
    public function testWithArray(): void
    {
        $phpBlock = /** @lang InjectablePHP */ '
            /** 
             * @psalm-return array<string, int> 
             */
            function getCollection(): array { return []; }
            
            $result = \Fp\Collection\pop(
                getCollection(),
            );
        ';

        $this->assertBlockType($phpBlock, strtr(
            'Option<Tuple2<int, list<int>>>',
            [
                'Option' => Option::class,
                'Tuple2' => Tuple2::class,
            ]
        ));
    }
}
