<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Option\Option;
use Generator;

use function Fp\Callable\asGenerator;

/**
 * @template TK
 * @template-covariant TV
 * @psalm-immutable
 * @extends AbstractOrderedMap<TK, TV>
 * @implements StaticStorage<empty>
 */
final class HashMap extends AbstractOrderedMap implements StaticStorage
{
    /**
     * @internal
     * @psalm-param HashTable<TK, TV> $hashTable
     */
    public function __construct(private HashTable $hashTable, private bool $empty) { }

    /**
     * @inheritDoc
     * @template TKI
     * @template TVI
     * @param iterable<TKI, TVI> $source
     * @return self<TKI, TVI>
     */
    public static function collect(iterable $source): self
    {
        return self::collectPairs(asGenerator(function () use ($source) {
            foreach ($source as $key => $value) {
                yield [$key, $value];
            }
        }));
    }

    /**
     * @template TKI
     * @template TVI
     * @param iterable<array{TKI, TVI}> $source
     * @return self<TKI, TVI>
     */
    public static function collectPairs(iterable $source): self
    {
        $buffer = new HashMapBuffer();

        foreach ($source as [$key, $value]) {
            $buffer->update($key, $value);
        }

        return $buffer->toHashMap();
    }

    /**
     * @return Generator<int, array{TK, TV}>
     */
    public function getIterator(): Generator
    {
        foreach ($this->hashTable->table as $bucket) {
            foreach ($bucket as $pair) {
                yield $pair;
            }
        }
    }

    /**
     * @return HashMap<TK, TV>
     */
    public function toHashMap(): HashMap
    {
        return $this;
    }

    /**
     * @inheritDoc
     * @param TK $key
     * @return Option<TV>
     */
    public function get(mixed $key): Option
    {
        $elem = null;

        $bucket = $this->findBucketByKey($key)->getOrElse([]);

        foreach ($bucket as [$k, $v]) {
            /** @psalm-suppress ImpureMethodCall */
            if (HashComparator::hashEquals($key, $k)) {
                $elem = $v;
            }
        }

        return Option::fromNullable($elem);
    }

    /**
     * @inheritDoc
     * @template TKI
     * @template TVI
     * @param TKI $key
     * @param TVI $value
     * @return self<TK|TKI, TV|TVI>
     */
    public function updated(mixed $key, mixed $value): self
    {
        return self::collectPairs([...$this->toArray(), [$key, $value]]);
    }

    /**
     * @inheritDoc
     * @param TK $key
     * @return self<TK, TV>
     */
    public function removed(mixed $key): self
    {
        return $this->filter(fn(Entry $e) => $e->key !== $key);
    }

    /**
     * @inheritDoc
     * @psalm-param callable(Entry<TK, TV>): bool $predicate
     * @psalm-return self<TK, TV>
     */
    public function filter(callable $predicate): self
    {
        return self::collectPairs(asGenerator(function () use ($predicate) {
            foreach ($this->generateEntries() as $entry) {
                if ($predicate($entry)) {
                    yield $entry->toArray();
                }
                unset($entry);
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVO
     * @param callable(Entry<TK, TV>): Option<TVO> $callback
     * @return self<TK, TVO>
     */
    public function filterMap(callable $callback): self
    {
        return self::collectPairs(asGenerator(function () use ($callback) {
            foreach ($this->generateEntries() as $entry) {
                $result = $callback($entry);

                if ($result->isSome()) {
                    yield [$entry->key, $result->get()];
                }

                unset($entry);
            }
        }));
    }

    /**
     * @experimental
     * @psalm-template TKO
     * @psalm-template TVO
     * @psalm-param callable(Entry<TK, TV>): iterable<array{TKO, TVO}> $callback
     * @psalm-return self<TKO, TVO>
     */
    public function flatMap(callable $callback): self
    {
        return self::collectPairs(asGenerator(function () use ($callback) {
            foreach ($this->generateEntries() as $entry) {
                foreach ($callback($entry) as $p) {
                    yield $p;
                }
                unset($entry);
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVO
     * @psalm-param callable(Entry<TK, TV>): TVO $callback
     * @psalm-return self<TK, TVO>
     */
    public function map(callable $callback): self
    {
        return $this->mapValues($callback);
    }

    /**
     * @inheritDoc
     * @template TVO
     * @psalm-param callable(Entry<TK, TV>): TVO $callback
     * @psalm-return self<TK, TVO>
     */
    public function mapValues(callable $callback): self
    {
        return self::collectPairs(asGenerator(function () use ($callback) {
            foreach ($this->generateEntries() as $entry) {
                yield [$entry->key, $callback($entry)];
                unset($entry);
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TKO
     * @psalm-param callable(Entry<TK, TV>): TKO $callback
     * @psalm-return self<TKO, TV>
     */
    public function mapKeys(callable $callback): self
    {
        return self::collectPairs(asGenerator(function () use ($callback) {
            foreach ($this->generateEntries() as $entry) {
                yield [$callback($entry), $entry->value];
                unset($entry);
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return Seq<TK>
     */
    public function keys(): Seq
    {
        return ArrayList::collect($this->generateKeys());
    }

    /**
     * @inheritDoc
     * @psalm-return Seq<TV>
     */
    public function values(): Seq
    {
        return ArrayList::collect($this->generateValues());
    }

    public function isEmpty():bool
    {
        return $this->empty;
    }

    /**
     * @param TK $key
     * @return Option<list<array{TK, TV}>>
     * @psalm-suppress ImpureMethodCall
     */
    private function findBucketByKey(mixed $key): Option
    {
        $hash = (string) HashComparator::computeHash($key);
        return Option::fromNullable($this->hashTable->table[$hash] ?? null);
    }
}
