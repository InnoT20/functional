<?php

declare(strict_types=1);

namespace Fp\Collections;

/**
 * @extends LinkedList<empty>
 */
final class Nil extends LinkedList
{
    private static ?Nil $instance = null;

    public static function getInstance(): Nil
    {
        return null === self::$instance ? self::$instance = new Nil() : self::$instance;
    }
}
