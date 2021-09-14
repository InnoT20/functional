<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Option\Option;
use Generator;
use Iterator;

/**
 * @psalm-immutable
 * @template TK
 * @template-covariant TV
 * @implements OrderedMap<TK, TV>
 */
abstract class AbstractOrderedMap implements OrderedMap
{
    /**
     * REPL:
     * >>> HashMap::collect(['a' =>  1, 'b' => 2])
     * => HashMap('a' -> 1, 'b' -> 2)
     *
     * @template TKI
     * @template TVI
     * @param iterable<TKI, TVI> $source
     * @return self<TKI, TVI>
     */
    abstract public static function collect(iterable $source): self;

    /**
     * REPL:
     * >>> HashMap::collectPairs([['a', 1], ['b', 2]])
     * => HashMap('a' -> 1, 'b' -> 2)
     *
     * @template TKI
     * @template TVI
     * @param iterable<array{TKI, TVI}> $source
     * @return self<TKI, TVI>
     */
    abstract public static function collectPairs(iterable $source): self;

    /**
     * @inheritDoc
     * @return Iterator<array{TK, TV}>
     */
    abstract public function getIterator(): Iterator;

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        $counter = 0;

        foreach ($this as $ignored) {
            $counter++;
        }

        return $counter;
    }

    /**
     * @inheritDoc
     * @return list<array{TK, TV}>
     */
    public function toArray(): array
    {
        $buffer = [];

        foreach ($this as $pair) {
            $buffer[] = $pair;
        }

        return $buffer;
    }

    /**
     * @inheritDoc
     * @return LinkedList<array{TK, TV}>
     */
    public function toLinkedList(): LinkedList
    {
        return LinkedList::collect($this->generatePairs());
    }

    /**
     * @return ArrayList<array{TK, TV}>
     */
    public function toArrayList(): ArrayList
    {
        return ArrayList::collect($this->generatePairs());
    }

    /**
     * @inheritDoc
     * @return HashSet<array{TK, TV}>
     */
    public function toHashSet(): HashSet
    {
        return HashSet::collect($this->generatePairs());
    }

    /**
     * @return HashMap<TK, TV>
     */
    abstract public function toHashMap(): HashMap;

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
     * @psalm-param callable(Entry<TK, TV>): bool $predicate
     */
    public function every(callable $predicate): bool
    {
        $result = true;

        foreach ($this->generateEntries() as $entry) {
            if (!$predicate($entry)) {
                $result = false;
                break;
            }
            unset($entry);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @template TA
     * @psalm-param TA $init initial accumulator value
     * @psalm-param callable(TA, Entry<TK, TV>): TA $callback (accumulator, current element): new accumulator
     * @psalm-return TA
     */
    public function fold(mixed $init, callable $callback): mixed
    {
        $acc = $init;

        foreach ($this->generateEntries() as $entry) {
            $acc = $callback($acc, $entry);
            unset($entry);
        }

        return $acc;
    }

    /**
     * @return Generator<array{TK, TV}>
     */
    protected function generatePairs(): Generator
    {
        foreach ($this as $pair) {
            yield $pair;
        }
    }

    /**
     * @return Generator<Entry<TK, TV>>
     */
    protected function generateEntries(): Generator
    {
        foreach ($this as [$key, $value]) {
            yield new Entry($key, $value);
        }
    }

    /**
     * @return Generator<TK>
     */
    public function generateKeys(): Generator
    {
        foreach ($this as $pair) {
            yield $pair[0];
        }
    }

    /**
     * @return Generator<TV>
     */
    public function generateValues(): Generator
    {
        foreach ($this as $pair) {
            yield $pair[1];
        }
    }
}
