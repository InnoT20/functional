<?php

declare(strict_types=1);

namespace Doc;

class MdHeader3 extends AbstractMdHeader
{
    public static function prefix(): string
    {
        return '### ';
    }
}
