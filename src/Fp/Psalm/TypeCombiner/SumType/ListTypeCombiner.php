<?php

declare(strict_types=1);

namespace Fp\Psalm\TypeCombiner\SumType;

use Fp\Psalm\TypeCombiner\TypeCombinerInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TList;
use Psalm\Type\Atomic\TNonEmptyList;
use Psalm\Type\Union;

use function Fp\Cast\asArray;
use function Fp\Cast\asList;
use function Fp\Collection\everyOf;
use function Fp\Collection\map;
use function Fp\Collection\any;

/**
 * @implements TypeCombinerInterface<TList>
 */
class ListTypeCombiner implements TypeCombinerInterface
{
    /**
     * @inheritdoc
     */
    public function supports(array $types): bool
    {
        return !empty($types) && everyOf($types, TList::class);
    }

    /**
     * @inheritdoc
     */
    public function combine(array $types): array
    {
        $hasPossiblyEmptyList = any($types, fn(TList $l) => $l::class === TList::class);
        $typeParams = asList(map($types, fn(TList $list) => $list->type_param));

        if (empty($typeParams)) {
            return asList($types);
        }

        $combinedTypeParams = Type::combineUnionTypeArray($typeParams, null);
        $atomics = asArray($combinedTypeParams->getAtomicTypes(), false);

        if (empty($atomics)) {
            return asList($types);
        }

        $reducedList = $hasPossiblyEmptyList
            ? new TList(new Union($atomics))
            : new TNonEmptyList(new Union($atomics));

        return [$reducedList];
    }
}
