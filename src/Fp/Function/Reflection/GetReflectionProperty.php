<?php

declare(strict_types=1);

namespace Fp\Function\Reflection;

use Fp\Functional\Either\Either;
use ReflectionException;
use ReflectionProperty;

/**
 * @psalm-return Either<ReflectionException, ReflectionProperty>
 */
function getReflectionProperty(object|string $class, string $property): Either
{
    return Either::try(fn() => new ReflectionProperty($class, $property));
}
