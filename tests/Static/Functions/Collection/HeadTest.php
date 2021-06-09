<?php

declare(strict_types=1);

namespace Tests\Static\Functions\Collection;

use Fp\Functional\Option\Option;
use Tests\PhpBlockTestCase;

final class HeadTest extends PhpBlockTestCase
{
    public function testWithArray(): void
    {
        $phpBlock = /** @lang InjectablePHP */ '
            /** 
             * @psalm-return array<string, int> 
             */
            function getCollection(): array { return []; }
            
            $result = \Fp\Collection\head(
                getCollection(),
            );
        ';

        $this->assertBlockTypes($phpBlock, 'Option<int>');
    }
}
