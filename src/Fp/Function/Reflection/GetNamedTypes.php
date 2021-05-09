<?php

declare(strict_types=1);

namespace Fp\Function\Reflection;

use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * @param ReflectionProperty $property
 * @return list<ReflectionNamedType>
 */
function getNamedTypes(ReflectionProperty $property): array
{
    $type = $property->getType();

    if (is_null($type)) {
        return [];
    }

    return match (true) {
        ($type instanceof ReflectionNamedType) => [$type],
        ($type instanceof ReflectionUnionType) => $type->getTypes(),
    };
}
