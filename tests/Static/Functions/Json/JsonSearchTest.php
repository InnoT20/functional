<?php

declare(strict_types=1);

namespace Tests\Static\Functions\Json;

use Fp\Functional\Option\Option;
use Tests\PhpBlockTestCase;

final class JsonSearchTest extends PhpBlockTestCase
{
    public function testSearch(): void
    {
        $phpBlock = /** @lang InjectablePHP */ '
            $json = "[]";
            $result = \Fp\Json\jsonSearch("[0]", $json);
        ';

        $this->assertBlockTypes(
            $phpBlock,
            'Option<array<array-key, mixed>|scalar>'
        );
    }
}
