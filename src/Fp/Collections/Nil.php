<?php

declare(strict_types=1);

namespace Fp\Collections;

/**
 * @psalm-immutable
 * @extends LinkedList<empty>
 */
final class Nil extends LinkedList
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     */
    public static function getInstance(): self
    {
        return is_null(self::$instance)
            ? self::$instance = new self()
            : self::$instance;
    }
}
