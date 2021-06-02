<?php

declare(strict_types=1);

namespace Tests\Static\Functions\Collection;

use Tests\PhpBlockTestCase;

final class FilterTest extends PhpBlockTestCase
{
    public function testPreserveKeysTrue(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-return array<string, int> 
             */
            function getCollection(): array { return []; }

            $result = filter(
                getCollection(),
                fn(int $v, string $k) => true,
                preserveKeys: true
            );
        ';

        $this->assertBlockTypes($phpBlock, 'array<string, int>');
    }

    public function testPreserveKeysExplicitFalse(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-return array<string, int> 
             */
            function getCollection(): array { return []; }

            $result = filter(
                getCollection(),
                fn(int $v, string $k) => true,
                preserveKeys: false
            );
        ';

        $this->assertBlockTypes($phpBlock, 'list<int>');
    }

    public function testPreserveKeysImplicitFalse(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-return array<string, int> 
             */
            function getCollection(): array { return []; }

            $result = filter(
                getCollection(),
                fn(int $v, string $k) => true,
            );
        ';

        $this->assertBlockTypes($phpBlock, 'list<int>');
    }

    public function testPreserveKeysIsNonLiteralBool(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-return array<string, int> 
             */
            function getCollection(): array { return []; }

            $result = filter(
                getCollection(),
                fn(int $v, string $k) => true,
                preserveKeys: (bool) rand(0, 1)
            );
        ';

        $this->assertBlockTypes($phpBlock, 'array<string, int>');
    }

    public function testRefineNotNull(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-return array<string, null|int> 
             */
            function getCollection(): array { return []; }

            $result = filter(
                getCollection(),
                fn(null|int $v) => null !== $v
            );
        ';

        $this->assertBlockTypes($phpBlock, 'list<int>');
    }

    public function testRefineShapeType(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-type Shape = array{name?: string, postcode?: int|string} 
             * @psalm-return array<string, Shape> 
             */
            function getCollection(): array { return []; }

            $result = filter(
                getCollection(),
                fn(array $v) => 
                    array_key_exists("name", $v) &&
                    array_key_exists("postcode", $v) &&
                    is_int($v["postcode"])
            );
        ';

        $this->assertBlockTypes($phpBlock, 'list<array{name: string, postcode: int}>');
    }

    public function testRefineShapeWithPsalmAssert(): void
    {
        $phpBlock = /** @lang InjectablePHP */
            '
            use function Fp\Collection\filter;

            /** 
             * @psalm-return array<string, array> 
             */
            function getCollection(): array { return []; }

            /**
             * @psalm-type Shape = array{name: string, postcode: int}
             * @psalm-assert-if-true Shape $shape
             */
            function isValidShape(array $shape): bool
            {
                return array_key_exists("name", $shape) && 
                    array_key_exists("postcode", $shape) &&
                    is_int($shape["postcode"]);
            }

            $result = filter(
                getCollection(),
                fn(array $v) => isValidShape($v)
            );
        ';

        $this->assertBlockTypes($phpBlock, 'list<array{name: string, postcode: int}>');
    }
}
