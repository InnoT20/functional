<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Functional\Separated\Separated;
use Fp\Operations as Ops;
use Fp\Operations\FoldOperation;
use Fp\Streams\Stream;
use Generator;

use function Fp\Callable\dropFirstArg;
use function Fp\Cast\asGenerator;

/**
 * @template TK
 * @template-covariant TV
 * @implements NonEmptyMap<TK, TV>
 *
 * @psalm-seal-methods
 * @mixin NonEmptyHashMapExtensions<TK, TV>
 *
 * @psalm-suppress InvalidTemplateParam
 */
final class NonEmptyHashMap implements NonEmptyMap
{
    /**
     * @internal
     * @param HashMap<TK, TV> $hashMap
     */
    public function __construct(private readonly HashMap $hashMap)
    {
    }

    #region NonEmptyMapCollectorOps

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param iterable<TKI, TVI> $source
     * @return Option<NonEmptyHashMap<TKI, TVI>>
     */
    public static function collect(iterable $source): Option
    {
        return NonEmptyHashMap::collectPairs(asGenerator(function () use ($source) {
            foreach ($source as $key => $value) {
                yield [$key, $value];
            }
        }));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param iterable<TKI, TVI> $source
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public static function collectUnsafe(iterable $source): NonEmptyHashMap
    {
        return NonEmptyHashMap::collect($source)->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param non-empty-array<TKI, TVI> $source
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public static function collectNonEmpty(array $source): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectUnsafe($source);
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param (iterable<mixed, array{TKI, TVI}>|Collection<mixed, array{TKI, TVI}>) $source
     * @return Option<NonEmptyHashMap<TKI, TVI>>
     */
    public static function collectPairs(iterable $source): Option
    {
        /**
         * @psalm-var HashTable<TKI, TVI> $hashTable
         */
        $hashTable = new HashTable();

        foreach ($source as [$key, $value]) {
            $hashTable->update($key, $value);
        }

        return Option::some($hashTable)
            ->filter(fn($hs) => !empty($hs->table))
            ->map(fn($hs) => new HashMap($hs))
            ->map(fn($hs) => new NonEmptyHashMap($hs));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param (iterable<mixed, array{TKI, TVI}>|Collection<mixed, array{TKI, TVI}>) $source
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public static function collectPairsUnsafe(iterable $source): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairs($source)->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param non-empty-array<array-key, array{TKI, TVI}>|NonEmptyCollection<mixed, array{TKI, TVI}> $source
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public static function collectPairsNonEmpty(array|NonEmptyCollection $source): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairsUnsafe($source);
    }

    #endregion NonEmptyMapCollectorOps

    #region NonEmptyMapCastableOps

    /**
     * {@inheritDoc}
     *
     * @return list<array{TK, TV}>
     */
    public function toList(): array
    {
        return $this->hashMap->toList();
    }

    /**
     * {@inheritDoc}
     *
     * @return non-empty-list<array{TK, TV}>
     */
    public function toNonEmptyList(): array
    {
        /** @var non-empty-list<array{TK, TV}> */
        return $this->toList();
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TKO, TVO>
     *
     * @return array<TKO, TVO>
     */
    public function toArray(): array
    {
        return $this->hashMap->toArray();
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TKO, TVO>
     *
     * @return non-empty-array<TKO, TVO>
     */
    public function toNonEmptyArray(): array
    {
        return $this->hashMap->toNonEmptyArray()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return LinkedList<array{TK, TV}>
     */
    public function toLinkedList(): LinkedList
    {
        return $this->hashMap->toLinkedList();
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyLinkedList<array{TK, TV}>
     */
    public function toNonEmptyLinkedList(): NonEmptyLinkedList
    {
        return $this->hashMap->toNonEmptyLinkedList()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayList<array{TK, TV}>
     */
    public function toArrayList(): ArrayList
    {
        return $this->hashMap->toArrayList();
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyArrayList<array{TK, TV}>
     */
    public function toNonEmptyArrayList(): NonEmptyArrayList
    {
        return $this->hashMap->toNonEmptyArrayList()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return HashSet<array{TK, TV}>
     */
    public function toHashSet(): HashSet
    {
        return $this->hashMap->toHashSet();
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyHashSet<array{TK, TV}>
     */
    public function toNonEmptyHashSet(): NonEmptyHashSet
    {
        return $this->hashMap->toNonEmptyHashSet()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return HashMap<TK, TV>
     */
    public function toHashMap(): HashMap
    {
        return $this->hashMap;
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyHashMap<TK, TV>
     */
    public function toNonEmptyHashMap(): NonEmptyHashMap
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TK, array<TKO, TVO>>
     *
     * @return array<TKO, TVO>
     */
    public function toMergedArray(): array
    {
        return $this->hashMap->toMergedArray();
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TK, non-empty-array<TKO, TVO>>
     *
     * @return non-empty-array<TKO, TVO>
     */
    public function toNonEmptyMergedArray(): array
    {
        return $this->hashMap->toNonEmptyMergedArray()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<array{TK, TV}>
     */
    public function toStream(): Stream
    {
        return $this->hashMap->toStream();
    }

    public function toString(): string
    {
        return (string) $this;
    }

    #endregion NonEmptyMapCastableOps

    #region NonEmptyMapChainableOps

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     *
     * @param TKI $key
     * @param TVI $value
     * @return NonEmptyHashMap<TK|TKI, TV|TVI>
     */
    public function appended(mixed $key, mixed $value): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->appended($key, $value));
    }

    /**
     * @template TKO
     * @template TVO
     *
     * @param Map<TKO, TVO>|NonEmptyMap<TKO, TVO>|iterable<TKO, TVO> $map
     * @return NonEmptyHashMap<TK|TKO, TV|TVO>
     */
    public function appendedAll(iterable $map): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->appendedAll($map));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TK, non-empty-array<TKO, TVO>|NonEmptyCollection<TKO, TVO>>
     *
     * @return NonEmptyHashMap<TKO, TVO>
     */
    public function flatten(): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->flatten());
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(TV): (non-empty-array<TKO, TVO>|NonEmptyCollection<TKO, TVO>) $callback
     * @return NonEmptyHashMap<TKO, TVO>
     */
    public function flatMap(callable $callback): NonEmptyHashMap
    {
        return $this->flatMapKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(mixed...): (non-empty-array<TKO, TVO>|NonEmptyCollection<TKO, TVO>) $callback
     * @return NonEmptyHashMap<TKO, TVO>
     */
    public function flatMapN(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->flatMapN($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(TK, TV): (non-empty-array<TKO, TVO>|NonEmptyCollection<TKO, TVO>) $callback
     * @return NonEmptyHashMap<TKO, TVO>
     */
    public function flatMapKV(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->flatMapKV($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): TVO $callback
     * @return NonEmptyHashMap<TK, TVO>
     */
    public function map(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->map($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TK, TV): TVO $callback
     * @return NonEmptyHashMap<TK, TVO>
     */
    public function mapKV(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->mapKV($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(mixed...): TVO $callback
     * @return NonEmptyHashMap<TK, TVO>
     */
    public function mapN(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->mapN($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): void $callback
     * @return NonEmptyHashMap<TK, TV>
     */
    public function tap(callable $callback): NonEmptyHashMap
    {
        return $this->tapKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(mixed...): void $callback
     * @return NonEmptyHashMap<TK, TV>
     */
    public function tapN(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->tapN($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TK, TV): void $callback
     * @return NonEmptyHashMap<TK, TV>
     */
    public function tapKV(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->tapKV($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(TV): TKO $callback
     * @return NonEmptyHashMap<TKO, TV>
     */
    public function reindex(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->reindex($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(mixed...): TKO $callback
     * @return NonEmptyHashMap<TKO, TV>
     */
    public function reindexN(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->reindexN($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(TK, TV): TKO $callback
     * @return NonEmptyHashMap<TKO, TV>
     */
    public function reindexKV(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->reindexKV($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(TV): TKO $callback
     * @return NonEmptyHashMap<TKO, NonEmptyHashMap<TK, TV>>
     */
    public function groupBy(callable $callback): NonEmptyHashMap
    {
        return $this->groupByKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(TK, TV): TKO $callback
     * @return NonEmptyHashMap<TKO, NonEmptyHashMap<TK, TV>>
     */
    public function groupByKV(callable $callback): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->groupByKV($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(TV): TKO $group
     * @param callable(TV): TVO $map
     * @return NonEmptyHashMap<TKO, NonEmptyHashMap<TK, TVO>>
     */
    public function groupMap(callable $group, callable $map): NonEmptyHashMap
    {
        return $this->groupMapKV(dropFirstArg($group), dropFirstArg($map));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(TK, TV): TKO $group
     * @param callable(TK, TV): TVO $map
     * @return NonEmptyHashMap<TKO, NonEmptyHashMap<TK, TVO>>
     */
    public function groupMapKV(callable $group, callable $map): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->groupMapKV($group, $map));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(TV): TKO $group
     * @param callable(TV): TVO $map
     * @param callable(TVO, TVO): TVO $reduce
     *
     * @return NonEmptyHashMap<TKO, TVO>
     */
    public function groupMapReduce(callable $group, callable $map, callable $reduce): NonEmptyHashMap
    {
        return $this->groupMapReduceKV(dropFirstArg($group), dropFirstArg($map), $reduce);
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     *
     * @param callable(TK, TV): TKO $group
     * @param callable(TK, TV): TVO $map
     * @param callable(TVO, TVO): TVO $reduce
     *
     * @return NonEmptyHashMap<TKO, TVO>
     */
    public function groupMapReduceKV(callable $group, callable $map, callable $reduce): NonEmptyHashMap
    {
        return new NonEmptyHashMap($this->hashMap->groupMapReduceKV($group, $map, $reduce));
    }

    #endregion NonEmptyMapChainableOps

    #region NonEmptyMapTerminalOps

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     */
    public function every(callable $predicate): bool
    {
        return $this->everyKV(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(mixed...): bool $predicate
     */
    public function everyN(callable $predicate): bool
    {
        return $this->hashMap->everyN($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TK, TV): bool $predicate
     */
    public function everyKV(callable $predicate): bool
    {
        return $this->hashMap->everyKV($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     */
    public function exists(callable $predicate): bool
    {
        return $this->existsKV(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(mixed...): bool $predicate
     */
    public function existsN(callable $predicate): bool
    {
        return $this->hashMap->existsN($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TK, TV): bool $predicate
     */
    public function existsKV(callable $predicate): bool
    {
        return $this->hashMap->existsKV($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return Option<NonEmptyHashMap<TK, TVO>>
     */
    public function traverseOption(callable $callback): Option
    {
        return $this->traverseOptionKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(mixed...): Option<TVO> $callback
     * @return Option<NonEmptyHashMap<TK, TVO>>
     */
    public function traverseOptionN(callable $callback): Option
    {
        return $this->hashMap->traverseOptionN($callback)->map(fn($map) => new NonEmptyHashMap($map));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TK, TV): Option<TVO> $callback
     * @return Option<NonEmptyHashMap<TK, TVO>>
     */
    public function traverseOptionKV(callable $callback): Option
    {
        return $this->hashMap
            ->traverseOptionKV($callback)
            ->flatMap(fn(HashMap $hs) => $hs->toNonEmptyHashMap());
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TK, Option<TVO>>
     *
     * @return Option<NonEmptyHashMap<TK, TVO>>
     */
    public function sequenceOption(): Option
    {
        return $this->hashMap
            ->sequenceOption()
            ->flatMap(fn(HashMap $hs) => $hs->toNonEmptyHashMap());
    }

    /**
     * {@inheritDoc}
     *
     * @template E
     * @template TVO
     *
     * @param callable(TV): Either<E, TVO> $callback
     * @return Either<E, NonEmptyHashMap<TK, TVO>>
     */
    public function traverseEither(callable $callback): Either
    {
        return $this->traverseEitherKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template E
     * @template TVO
     *
     * @param callable(mixed...): Either<E, TVO> $callback
     * @return Either<E, NonEmptyHashMap<TK, TVO>>
     */
    public function traverseEitherN(callable $callback): Either
    {
        return $this->hashMap->traverseEitherN($callback)->map(fn($map) => new NonEmptyHashMap($map));
    }

    /**
     * {@inheritDoc}
     *
     * @template E
     * @template TVO
     *
     * @param callable(TK, TV): Either<E, TVO> $callback
     * @return Either<E, NonEmptyHashMap<TK, TVO>>
     */
    public function traverseEitherKV(callable $callback): Either
    {
        return $this->hashMap->traverseEitherKV($callback)
            ->map(fn(HashMap $hs) => new NonEmptyHashMap($hs));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Separated<HashMap<TK, TV>, HashMap<TK, TV>>
     */
    public function partition(callable $predicate): Separated
    {
        return $this->partitionKV(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(mixed...): bool $predicate
     * @return Separated<HashMap<TK, TV>, HashMap<TK, TV>>
     */
    public function partitionN(callable $predicate): Separated
    {
        return $this->hashMap->partitionN($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TK, TV): bool $predicate
     * @return Separated<HashMap<TK, TV>, HashMap<TK, TV>>
     */
    public function partitionKV(callable $predicate): Separated
    {
        return $this->hashMap->partitionKV($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @template LO
     * @template RO
     *
     * @param callable(TV): Either<LO, RO> $callback
     * @return Separated<HashMap<TK, LO>, HashMap<TK, RO>>
     */
    public function partitionMap(callable $callback): Separated
    {
        return $this->partitionMapKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template LO
     * @template RO
     *
     * @param callable(mixed...): Either<LO, RO> $callback
     * @return Separated<HashMap<TK, LO>, HashMap<TK, RO>>
     */
    public function partitionMapN(callable $callback): Separated
    {
        return $this->hashMap->partitionMapN($callback);
    }

    /**
     * {@inheritDoc}
     *
     * @template LO
     * @template RO
     *
     * @param callable(TK, TV): Either<LO, RO> $callback
     * @return Separated<HashMap<TK, LO>, HashMap<TK, RO>>
     */
    public function partitionMapKV(callable $callback): Separated
    {
        return $this->hashMap->partitionMapKV($callback);
    }

    /**
     * {@inheritDoc}
     *
     * @template E
     * @template TVO
     * @psalm-if-this-is NonEmptyHashMap<TK, Either<E, TVO>>
     *
     * @return Either<E, NonEmptyHashMap<TK, TVO>>
     */
    public function sequenceEither(): Either
    {
        return $this->hashMap->sequenceEither()
            ->map(fn(HashMap $hs) => new NonEmptyHashMap($hs));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param TVO $init
     * @return FoldOperation<TV, TVO>
     */
    public function fold(mixed $init): FoldOperation
    {
        return new FoldOperation($this, $init);
    }

    /**
     * {@inheritDoc}
     *
     * @param TK $key
     * @return Option<TV>
     */
    public function get(mixed $key): Option
    {
        return $this->hashMap->get($key);
    }

    /**
     * {@inheritDoc}
     *
     * @param TK $key
     * @return HashMap<TK, TV>
     */
    public function removed(mixed $key): HashMap
    {
        return $this->hashMap->removed($key);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return HashMap<TK, TV>
     */
    public function filter(callable $predicate): HashMap
    {
        return $this->hashMap->filter($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(mixed...): bool $predicate
     * @return HashMap<TK, TV>
     */
    public function filterN(callable $predicate): HashMap
    {
        return $this->hashMap->filterN($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TK, TV): bool $predicate
     * @return Map<TK, TV>
     */
    public function filterKV(callable $predicate): Map
    {
        return $this->hashMap->filterKV($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return HashMap<TK, TVO>
     */
    public function filterMap(callable $callback): HashMap
    {
        return $this->filterMapKV(dropFirstArg($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(mixed...): Option<TVO> $callback
     * @return HashMap<TK, TVO>
     */
    public function filterMapN(callable $callback): HashMap
    {
        return $this->hashMap->filterMapN($callback);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TK, TV): Option<TVO> $callback
     * @return HashMap<TK, TVO>
     */
    public function filterMapKV(callable $callback): HashMap
    {
        return $this->hashMap->filterMapKV($callback);
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyArrayList<TK>
     */
    public function keys(): NonEmptyArrayList
    {
        return $this->hashMap->keys()
            ->toNonEmptyArrayList()
            ->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyArrayList<TV>
     */
    public function values(): NonEmptyArrayList
    {
        return $this->hashMap->values()
            ->toNonEmptyArrayList()
            ->getUnsafe();
    }

    #endregion NonEmptyMapTerminalOps

    #region Traversable

    /**
     * @return Generator<TK, TV>
     */
    public function getIterator(): Generator
    {
        return $this->hashMap->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->hashMap->count();
    }

    #endregion Traversable

    #region Magic methods

    public function __toString(): string
    {
        return $this
            ->mapKV(fn($key, $value) => Ops\ToStringOperation::of($key) . ' => ' . Ops\ToStringOperation::of($value))
            ->values()
            ->toArrayList()
            ->mkString('NonEmptyHashMap(', ', ', ')');
    }

    /**
     * {@inheritDoc}
     *
     * @param TK $key
     * @return Option<TV>
     */
    public function __invoke(mixed $key): Option
    {
        return $this->get($key);
    }

    /**
     * @param non-empty-string $name
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return NonEmptyHashMapExtensions::call($this, $name, $arguments);
    }

    /**
     * @param non-empty-string $name
     * @param list<mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return NonEmptyHashMapExtensions::callStatic($name, $arguments);
    }

    #endregion Magic methods
}
