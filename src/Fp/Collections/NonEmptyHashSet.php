<?php

declare(strict_types=1);

namespace Fp\Collections;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Functional\Separated\Separated;
use Fp\Operations as Ops;
use Fp\Operations\FoldOperation;
use Fp\Streams\Stream;
use Iterator;

use function Fp\Callable\dropFirstArg;
use function Fp\Callable\toSafeClosure;
use function Fp\Cast\asNonEmptyList;
use function Fp\Cast\fromPairs;

/**
 * @template-covariant TV
 * @implements NonEmptySet<TV>
 *
 * @psalm-seal-methods
 * @mixin NonEmptyHashSetExtensions<TV>
 *
 * @psalm-suppress InvalidTemplateParam
 */
final class NonEmptyHashSet implements NonEmptySet
{
    /**
     * @internal
     * @param HashSet<TV> $set
     */
    public function __construct(private readonly HashSet $set)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param (iterable<TVI>|Collection<TVI>) $source
     * @return Option<NonEmptyHashSet<TVI>>
     */
    public static function collect(iterable $source): Option
    {
        return Option::some(HashSet::collect($source))
            ->filter(fn($hs) => !$hs->isEmpty())
            ->map(fn($hs) => new NonEmptyHashSet($hs));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $val
     * @return NonEmptyHashSet<TVI>
     */
    public static function singleton(mixed $val): NonEmptyHashSet
    {
        return NonEmptyHashSet::collectNonEmpty([$val]);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param (iterable<TVI>|Collection<TVI>) $source
     * @return NonEmptyHashSet<TVI>
     */
    public static function collectUnsafe(iterable $source): NonEmptyHashSet
    {
        return NonEmptyHashSet::collect($source)->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param non-empty-array<array-key, TVI>|NonEmptyCollection<TVI> $source
     * @return NonEmptyHashSet<TVI>
     */
    public static function collectNonEmpty(array|NonEmptyCollection $source): NonEmptyHashSet
    {
        return NonEmptyHashSet::collectUnsafe($source);
    }

    /**
     * @return Iterator<int, TV>
     */
    public function getIterator(): Iterator
    {
        return $this->set->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return Ops\CountOperation::of($this->getIterator())();
    }

    /**
     * {@inheritDoc}
     *
     * @return list<TV>
     */
    public function toList(): array
    {
        return asNonEmptyList($this->getIterator())->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return non-empty-list<TV>
     */
    public function toNonEmptyList(): array
    {
        /** @var non-empty-list<TV> */
        return $this->toList();
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptySet<array{TKO, TVO}>
     *
     * @return array<TKO, TVO>
     */
    public function toArray(): array
    {
        return fromPairs($this);
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptySet<array{TKO, TVO}>
     *
     * @return non-empty-array<TKO, TVO>
     */
    public function toNonEmptyArray(): array
    {
        /** @var non-empty-array<TKO, TVO> */
        return $this->toArray();
    }

    /**
     * {@inheritDoc}
     *
     * @return LinkedList<TV>
     */
    public function toLinkedList(): LinkedList
    {
        return LinkedList::collect($this);
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayList<TV>
     */
    public function toArrayList(): ArrayList
    {
        return ArrayList::collect($this);
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyLinkedList<TV>
     */
    public function toNonEmptyLinkedList(): NonEmptyLinkedList
    {
        return NonEmptyLinkedList::collectUnsafe($this);
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyArrayList<TV>
     */
    public function toNonEmptyArrayList(): NonEmptyArrayList
    {
        return NonEmptyArrayList::collectUnsafe($this);
    }

    /**
     * {@inheritDoc}
     *
     * @return HashSet<TV>
     */
    public function toHashSet(): HashSet
    {
        return $this->set;
    }

    /**
     * {@inheritDoc}
     *
     * @return NonEmptyHashSet<TV>
     */
    public function toNonEmptyHashSet(): NonEmptyHashSet
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     * @psalm-if-this-is NonEmptyHashSet<array{TKI, TVI}>
     *
     * @return HashMap<TKI, TVI>
     */
    public function toHashMap(): HashMap
    {
        return HashMap::collectPairs($this);
    }

    /**
     * {@inheritDoc}
     *
     * @template TKI
     * @template TVI
     * @psalm-if-this-is NonEmptyHashSet<array{TKI, TVI}>
     *
     * @return NonEmptyHashMap<TKI, TVI>
     */
    public function toNonEmptyHashMap(): NonEmptyHashMap
    {
        return NonEmptyHashMap::collectPairsNonEmpty($this);
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function toStream(): Stream
    {
        return Stream::emits($this);
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is NonEmptyHashSet<array<TKO, TVO>>
     *
     * @return non-empty-array<TKO, TVO>
     */
    public function toMergedArray(): array
    {
        /** @var non-empty-array<TKO, TVO> */
        return array_merge(...$this->toNonEmptyList());
    }

    /**
     * {@inheritDoc}
     * @param TV $element
     */
    public function __invoke(mixed $element): bool
    {
        return $this->contains($element);
    }

    /**
     * {@inheritDoc}
     * @param callable(TV): bool $predicate
     */
    public function every(callable $predicate): bool
    {
        return Ops\EveryOperation::of($this->getIterator())(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     * @psalm-assert-if-true Set<TVO> $this
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     */
    public function everyOf(string|array $fqcn, bool $invariant = false): bool
    {
        return Ops\EveryOfOperation::of($this->getIterator())($fqcn, $invariant);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return Option<NonEmptyHashSet<TVO>>
     */
    public function traverseOption(callable $callback): Option
    {
        return Ops\TraverseOptionOperation::of($this->getIterator())(dropFirstArg($callback))
            ->map(fn($gen) => NonEmptyHashSet::collectUnsafe($gen));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     * @psalm-if-this-is NonEmptyHashSet<Option<TVO>>
     *
     * @return Option<NonEmptyHashSet<TVO>>
     */
    public function sequenceOption(): Option
    {
        return Ops\TraverseOptionOperation::id($this->getIterator())
            ->map(fn($gen) => NonEmptyHashSet::collectUnsafe($gen));
    }

    /**
     * {@inheritDoc}
     *
     * @template E
     * @template TVO
     *
     * @param callable(TV): Either<E, TVO> $callback
     * @return Either<E, NonEmptyHashSet<TVO>>
     */
    public function traverseEither(callable $callback): Either
    {
        return Ops\TraverseEitherOperation::of($this->getIterator())(dropFirstArg($callback))
            ->map(fn($gen) => NonEmptyHashSet::collectUnsafe($gen));
    }

    /**
     * {@inheritDoc}
     *
     * @template E
     * @template TVO
     * @psalm-if-this-is NonEmptyHashSet<Either<E, TVO>>
     *
     * @return Either<E, NonEmptyHashSet<TVO>>
     */
    public function sequenceEither(): Either
    {
        return Ops\TraverseEitherOperation::id($this->getIterator())
            ->map(fn($gen) => NonEmptyHashSet::collectUnsafe($gen));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Separated<HashSet<TV>, HashSet<TV>>
     */
    public function partition(callable $predicate): Separated
    {
        return Ops\PartitionOperation::of($this->getIterator())(dropFirstArg($predicate))
            ->mapLeft(fn($left) => HashSet::collect($left))
            ->map(fn($right) => HashSet::collect($right));
    }

    /**
     * {@inheritDoc}
     *
     * @template LO
     * @template RO
     *
     * @param callable(TV): Either<LO, RO> $callback
     * @return Separated<HashSet<LO>, HashSet<RO>>
     */
    public function partitionMap(callable $callback): Separated
    {
        return Ops\PartitionMapOperation::of($this->getIterator())(dropFirstArg($callback))
            ->mapLeft(fn($left) => HashSet::collect($left))
            ->map(fn($right) => HashSet::collect($right));
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
        return new NonEmptyHashMap(
            HashMap::collect(Ops\ReindexOperation::of($this->getIterator())(dropFirstArg($callback))),
        );
    }

    /**
     * {@inheritDoc}
     * @param callable(TV): bool $predicate
     */
    public function exists(callable $predicate): bool
    {
        return Ops\ExistsOperation::of($this->getIterator())(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     */
    public function existsOf(string|array $fqcn, bool $invariant = false): bool
    {
        return Ops\ExistsOfOperation::of($this->getIterator())($fqcn, $invariant);
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(TV): TKO $callback
     * @return NonEmptyMap<TKO, NonEmptyHashSet<TV>>
     */
    public function groupBy(callable $callback): NonEmptyMap
    {
        $groups = Ops\GroupByOperation::of($this->getIterator())(dropFirstArg($callback));

        return (new NonEmptyHashMap($groups))
            ->map(fn(NonEmptyHashMap $seq) => $seq->values()->toNonEmptyHashSet());
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Option<TV>
     */
    public function first(callable $predicate): Option
    {
        return Ops\FirstOperation::of($this->getIterator())(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Option<TV>
     */
    public function last(callable $predicate): Option
    {
        return Ops\LastOperation::of($this->getIterator())(dropFirstArg($predicate));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     * @return Option<TVO>
     */
    public function firstOf(string|array $fqcn, bool $invariant = false): Option
    {
        return Ops\FirstOfOperation::of($this->getIterator())($fqcn, $invariant);
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
        return new FoldOperation($this->getIterator(), $init);
    }

    /**
     * {@inheritDoc}
     *
     * @return TV
     */
    public function head(): mixed
    {
        return Ops\HeadOperation::of($this->getIterator())()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return TV
     */
    public function firstElement(): mixed
    {
        return Ops\FirstOperation::of($this->getIterator())()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return TV
     */
    public function lastElement(): mixed
    {
        return Ops\LastOperation::of($this->getIterator())()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @param TV $element
     */
    public function contains(mixed $element): bool
    {
        return $this->set->contains($element);
    }

    /**
     * {@inheritDoc}
     *
     * @return HashSet<TV>
     */
    public function tail(): HashSet
    {
        return $this->set->tail();
    }

    /**
     * {@inheritDoc}
     *
     * @return HashSet<TV>
     */
    public function init(): HashSet
    {
        return HashSet::collect(Ops\InitOperation::of($this->getIterator())());
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $element
     * @return NonEmptyHashSet<TV|TVI>
     */
    public function updated(mixed $element): NonEmptyHashSet
    {
        return new NonEmptyHashSet($this->set->appended($element));
    }

    /**
     * {@inheritDoc}
     *
     * @param TV $element
     * @return HashSet<TV>
     */
    public function removed(mixed $element): HashSet
    {
        return $this->set->removed($element);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return HashSet<TV>
     */
    public function filter(callable $predicate): HashSet
    {
        return $this->set->filter($predicate);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO>|list<class-string<TVO>> $fqcn
     * @return HashSet<TVO>
     */
    public function filterOf(string|array $fqcn, bool $invariant = false): HashSet
    {
        return $this->set->filterOf($fqcn, $invariant);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return HashSet<TVO>
     */
    public function filterMap(callable $callback): HashSet
    {
        return $this->set->filterMap($callback);
    }

    /**
     * {@inheritDoc}
     *
     * @return HashSet<TV>
     */
    public function filterNotNull(): HashSet
    {
        return $this->filter(fn($elem) => null !== $elem);
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     * @psalm-if-this-is NonEmptyHashSet<iterable<TVO>|Collection<TVO>>
     *
     * @return Set<TVO>
     */
    public function flatten(): Set
    {
        return $this->set->flatten();
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): TVO $callback
     * @return NonEmptyHashSet<TVO>
     */
    public function map(callable $callback): NonEmptyHashSet
    {
        return NonEmptyHashSet::collectUnsafe(Ops\MapOperation::of($this->getIterator())(dropFirstArg($callback)));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(mixed...): TVO $callback
     * @return NonEmptyHashSet<TVO>
     */
    public function mapN(callable $callback): NonEmptyHashSet
    {
        return $this->map(function($tuple) use ($callback) {
            /** @var array $tuple */;
            return toSafeClosure($callback)(...$tuple);
        });
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): (iterable<TVO>|Collection<TVO>) $callback
     * @return HashSet<TVO>
     */
    public function flatMap(callable $callback): HashSet
    {
        return $this->set->flatMap($callback);
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): void $callback
     * @return NonEmptyHashSet<TV>
     */
    public function tap(callable $callback): NonEmptyHashSet
    {
        Stream::emits(Ops\TapOperation::of($this->getIterator())(dropFirstArg($callback)))->drain();
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function subsetOf(Set|NonEmptySet $superset): bool
    {
        $isSubset = true;

        foreach ($this as $elem) {
            if (!$superset($elem)) {
                $isSubset = false;
                break;
            }
        }

        return $isSubset;
    }

    /**
     * {@inheritDoc}
     *
     * @param Set<TV>|NonEmptySet<TV> $that
     * @return Set<TV>
     */
    public function intersect(Set|NonEmptySet $that): Set
    {
        return $this->filter(fn($elem) => /** @var TV $elem */ $that($elem));
    }

    /**
     * {@inheritDoc}
     *
     * @param Set<TV>|NonEmptySet<TV> $that
     * @return Set<TV>
     */
    public function diff(Set|NonEmptySet $that): Set
    {
        return $this->filter(fn($elem) => /** @var TV $elem */ !$that($elem));
    }

    /**
     * {@inheritDoc}
     *
     * @return TV
     */
    public function max(): mixed
    {
        return $this->set->max()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): mixed $callback
     * @return TV
     */
    public function maxBy(callable $callback): mixed
    {
        return $this->set->maxBy($callback)->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @return TV
     */
    public function min(): mixed
    {
        return $this->set->min()->getUnsafe();
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): mixed $callback
     * @return TV
     */
    public function minBy(callable $callback): mixed
    {
        return $this->set->minBy($callback)->getUnsafe();
    }

    public function toString(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return $this
            ->map(Ops\ToStringOperation::of(...))
            ->toArrayList()
            ->mkString('NonEmptyHashSet(', ', ', ')');
    }
}
