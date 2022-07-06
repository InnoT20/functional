<?php

declare(strict_types=1);

namespace Fp\Operations;

use Fp\Collections\HashTable;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Generator;

/**
 * @template TK
 * @template TV
 *
 * @extends AbstractOperation<TK, TV>
 */
final class TraverseEitherOperation extends AbstractOperation
{
    /**
     * @template E
     * @template TVO
     *
     * @param callable(TV): Either<E, TVO> $f
     * @return Either<E, Generator<TK, TVO>>
     */
    public function __invoke(callable $f): Either
    {
        /** @psalm-var HashTable<TK, TVO> $hashTable */
        $hashTable = new HashTable();

        foreach ($this->gen as $key => $value) {
            $mapped = $f($value);

            if ($mapped->isLeft()) {
                return $mapped;
            }

            $hashTable->update($key, $mapped->get());
        }

        return Either::right($hashTable->getKeyValueIterator());
    }

    /**
     * @template E
     * @template TKI
     * @template TVI
     *
     * @param iterable<TKI, Either<E, TVI>> $collection
     * @return Either<E, Generator<TKI, TVI>>
     */
    public static function id(iterable $collection): Either
    {
        $id =
            /**
             * @param Either<E, TVI> $I
             * @return Either<E, TVI>
             */
            fn(Either $i): Either => $i;

        return self::of($collection)($id);
    }
}
