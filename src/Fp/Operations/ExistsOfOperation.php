<?php

declare(strict_types=1);

namespace Fp\Operations;

use function Fp\of;

/**
 * @template TK
 * @template TV
 * @psalm-immutable
 * @extends AbstractOperation<TK, TV>
 */
class ExistsOfOperation extends AbstractOperation
{
    /**
     * @template TVO
     * @param class-string<TVO> $fqcn fully qualified class name
     * @param bool $invariant if turned on then subclasses are not allowed
     * @return bool
     */
    public function __invoke(string $fqcn, bool $invariant = false): bool
    {
        return ExistsOperation::of($this->gen)(fn($elem) => of($elem, $fqcn, $invariant));
    }
}
