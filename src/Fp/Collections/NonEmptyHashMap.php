<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Option\Option;
use Fp\Operations\CountOperation;
use Fp\Operations\MapWithKeyOperation;
use Fp\Operations\MapOperation;
use Fp\Operations\ReindexOperation;
use Fp\Operations\ToStringOperation;
use Fp\Operations\TraverseOptionOperation;
use Fp\Operations\EveryOperation;
use Fp\Operations\KeysOperation;
use Fp\Operations\ReindexWithKeyOperation;
use Fp\Operations\ValuesOperation;
use Fp\Streams\Stream;
use Generator;

use function Fp\Cast\asGenerator;

/**
 * @template TK
 * @template-covariant TV
 * @psalm-suppress InvalidTemplateParam
 * @implements NonEmptyMap<TK, TV>
 */
final class NonEmptyHashMap implements NonEmptyMap
{
    /**
     * @internal
     * @param HashMap<TK, TV> $hashMap
     */
    public function __construct(private HashMap $hashMap)
    {
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     *
     * @template TKI
     * @template TVI
     *
     * @param iterable<array{TKI, TVI}> $source
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
     * @inheritDoc
     *
     * @template TKI
     * @template TVI
     *
     * @param iterable<array{TKI, TVI}> $source
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public static function collectPairsUnsafe(iterable $source): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairs($source)->getUnsafe();
    }

    /**
     * @inheritDoc
     *
     * @template TKI
     * @template TVI
     *
     * @param non-empty-array<array{TKI, TVI}>|NonEmptyCollection<array{TKI, TVI}> $source
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public static function collectPairsNonEmpty(array|NonEmptyCollection $source): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairsUnsafe($source);
    }

    /**
     * @return Generator<int, array{TK, TV}>
     */
    public function getIterator(): Generator
    {
        return $this->hashMap->getIterator();
    }

    /**
     * @return Generator<TK, TV>
     */
    public function getKeyValueIterator(): Generator
    {
        return $this->hashMap->getKeyValueIterator();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return CountOperation::of($this->getIterator())();
    }

    /**
     * @inheritDoc
     * @return list<array{TK, TV}>
     */
    public function toList(): array
    {
        return $this->toNonEmptyArrayList()->toNonEmptyList();
    }

    /**
     * @inheritDoc
     * @return non-empty-list<array{TK, TV}>
     */
    public function toNonEmptyList(): array
    {
        /** @var non-empty-list<array{TK, TV}> */
        return $this->toList();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     * @return LinkedList<array{TK, TV}>
     */
    public function toLinkedList(): LinkedList
    {
        return LinkedList::collect($this->getIterator());
    }

    /**
     * @inheritDoc
     * @return NonEmptyLinkedList<array{TK, TV}>
     */
    public function toNonEmptyLinkedList(): NonEmptyLinkedList
    {
        return NonEmptyLinkedList::collectUnsafe($this->getIterator());
    }

    /**
     * @inheritDoc
     * @return ArrayList<array{TK, TV}>
     */
    public function toArrayList(): ArrayList
    {
        return ArrayList::collect($this->getIterator());
    }

    /**
     * @inheritDoc
     * @return NonEmptyArrayList<array{TK, TV}>
     */
    public function toNonEmptyArrayList(): NonEmptyArrayList
    {
        return NonEmptyArrayList::collectUnsafe($this->getIterator());
    }

    /**
     * @inheritDoc
     * @return HashSet<array{TK, TV}>
     */
    public function toHashSet(): HashSet
    {
        return HashSet::collect($this->getIterator());
    }

    /**
     * @inheritDoc
     * @return NonEmptyHashSet<array{TK, TV}>
     */
    public function toNonEmptyHashSet(): NonEmptyHashSet
    {
        return NonEmptyHashSet::collectUnsafe($this->getIterator());
    }

    /**
     * @inheritDoc
     * @return HashMap<TK, TV>
     */
    public function toHashMap(): HashMap
    {
        return $this->hashMap;
    }

    /**
     * @inheritDoc
     * @return NonEmptyHashMap<TK, TV>
     */
    public function toNonEmptyHashMap(): NonEmptyHashMap
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<array{TK, TV}>
     */
    public function toStream(): Stream
    {
        return Stream::emits($this->getIterator());
    }

    /**
     * @inheritDoc
     *
     * @param callable(TV): bool $predicate
     */
    public function every(callable $predicate): bool
    {
        return EveryOperation::of($this->getKeyValueIterator())($predicate);
    }

    /**
     * @inheritDoc
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return Option<NonEmptyHashMap<TK, TVO>>
     */
    public function traverseOption(callable $callback): Option
    {
        return TraverseOptionOperation::of($this->getKeyValueIterator())($callback)
            ->map(fn($gen) => NonEmptyHashMap::collectUnsafe($gen));
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
        $iterator = $this->getKeyValueIterator();

        return TraverseOptionOperation::id($iterator)
            ->map(fn($gen) => NonEmptyHashMap::collectUnsafe($gen));
    }

    /**
     * @inheritDoc
     * @param TK $key
     * @return Option<TV>
     */
    public function __invoke(mixed $key): Option
    {
        return $this->get($key);
    }

    /**
     * @inheritDoc
     * @param TK $key
     * @return Option<TV>
     */
    public function get(mixed $key): Option
    {
        return $this->hashMap->get($key);
    }

    /**
     * @inheritDoc
     * @template TKI
     * @template TVI
     * @param TKI $key
     * @param TVI $value
     * @return NonEmptyHashMap<TK|TKI, TV|TVI>
     */
    public function updated(mixed $key, mixed $value): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairsUnsafe([...$this->toList(), [$key, $value]]);
    }

    /**
     * @inheritDoc
     * @param TK $key
     * @return HashMap<TK, TV>
     */
    public function removed(mixed $key): HashMap
    {
        return $this->hashMap->removed($key);
    }

    /**
     * @inheritDoc
     *
     * @param callable(TV): bool $predicate
     * @return HashMap<TK, TV>
     */
    public function filter(callable $predicate): HashMap
    {
        return $this->hashMap->filter($predicate);
    }

    /**
     * @inheritDoc
     *
     * @param callable(TK, TV): bool $predicate
     * @return Map<TK, TV>
     */
    public function filterKV(callable $predicate): Map
    {
        return $this->hashMap->filterKV($predicate);
    }

    /**
     * @inheritDoc
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return HashMap<TK, TVO>
     */
    public function filterMap(callable $callback): HashMap
    {
        return $this->hashMap->filterMap($callback);
    }

    /**
     * @inheritDoc
     *
     * @template TVO
     *
     * @param callable(TV): TVO $callback
     * @return NonEmptyHashMap<TK, TVO>
     */
    public function map(callable $callback): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectUnsafe(MapOperation::of($this->getKeyValueIterator())($callback));
    }

    /**
     * @inheritDoc
     *
     * @template TVO
     *
     * @param callable(TK, TV): TVO $callback
     * @return NonEmptyHashMap<TK, TVO>
     */
    public function mapKV(callable $callback): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectUnsafe(MapWithKeyOperation::of($this->getKeyValueIterator())($callback));
    }

    /**
     * @inheritDoc
     *
     * @template TKO
     *
     * @param callable(TV): TKO $callback
     * @return NonEmptyHashMap<TKO, TV>
     */
    public function reindex(callable $callback): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectUnsafe(ReindexOperation::of($this->getKeyValueIterator())($callback));
    }

    /**
     * @inheritDoc
     *
     * @template TKO
     *
     * @param callable(TK, TV): TKO $callback
     * @return NonEmptyHashMap<TKO, TV>
     */
    public function reindexKV(callable $callback): NonEmptyMap
    {
        return NonEmptyHashMap::collectUnsafe(ReindexWithKeyOperation::of($this->getKeyValueIterator())($callback));
    }

    /**
     * @inheritDoc
     * @return NonEmptySeq<TK>
     */
    public function keys(): NonEmptySeq
    {
        return NonEmptyArrayList::collectUnsafe(KeysOperation::of($this->getKeyValueIterator())());
    }

    /**
     * @inheritDoc
     * @return NonEmptySeq<TV>
     */
    public function values(): NonEmptySeq
    {
        return NonEmptyArrayList::collectUnsafe(ValuesOperation::of($this->getKeyValueIterator())());
    }

    public function __toString(): string
    {
        return $this
            ->mapKV(fn($key, $value) => ToStringOperation::of($key) . ' => ' . ToStringOperation::of($value))
            ->values()
            ->toArrayList()
            ->mkString('NonEmptyHashMap(', ', ', ')');
    }
}
