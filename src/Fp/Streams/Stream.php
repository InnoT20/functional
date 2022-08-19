<?php

declare(strict_types=1);

namespace Fp\Streams;

use Fp\Collections\ArrayList;
use Fp\Collections\HashMap;
use Fp\Collections\HashSet;
use Fp\Collections\LinkedList;
use Fp\Collections\NonEmptyArrayList;
use Fp\Collections\NonEmptyHashMap;
use Fp\Collections\NonEmptyHashSet;
use Fp\Collections\NonEmptyLinkedList;
use Fp\Collections\Seq;
use Fp\Functional\Option\Option;
use Fp\Functional\Unit;
use Fp\Operations as Ops;
use Generator;
use IteratorAggregate;
use LogicException;
use SplFileObject;

use function Fp\Callable\dropFirstArg;
use function Fp\Cast\asGenerator;
use function Fp\Cast\asArray;
use function Fp\Cast\asList;
use function Fp\Cast\asNonEmptyArray;
use function Fp\Cast\asNonEmptyList;

/**
 * Note: stream iteration via foreach is terminal operation
 *
 * @template-covariant TV
 * @implements StreamOps<TV>
 * @implements StreamEmitter<TV>
 * @implements IteratorAggregate<TV>
 *
 * @psalm-suppress InvalidTemplateParam
 */
final class Stream implements StreamOps, StreamEmitter, IteratorAggregate
{
    /**
     * @var Generator<int, TV>
     */
    private Generator $emitter;

    /**
     * @psalm-readonly-allow-private-mutation $forked
     */
    private bool $forked = false;

    /**
     * @psalm-readonly-allow-private-mutation $drained
     */
    private bool $drained = false;

    /**
     * @param iterable<TV> $emitter
     */
    private function __construct(iterable $emitter)
    {
        $this->emitter = asGenerator(function() use ($emitter): Generator {
            foreach ($emitter as $elem) {
                yield $elem;
            }
        });
    }

    /**
     * Note: You can not iterate the stream second time
     *
     * @return Generator<int, TV>
     */
    public function getIterator(): Generator
    {
        return $this->leaf($this->emitter);
    }

    /**
     * @template T
     *
     * @param T $iter
     * @return T
     */
    private function leaf(mixed $iter): mixed
    {
        $this->drained = $this->drained
            ? throw new LogicException('Can not drain already drained stream')
            : true;

        return $iter;
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $elem
     * @return Stream<TVI>
     */
    public static function emit(mixed $elem): Stream
    {
        return Stream::emits(asGenerator(function () use ($elem) {
            yield $elem;
        }));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param iterable<TVI> $source
     * @return Stream<TVI>
     */
    public static function emits(iterable $source): Stream
    {
        return new Stream($source);
    }

    /**
     * {@inheritDoc}
     *
     * @param int<0, max> $seconds
     * @return Stream<int>
     */
    public static function awakeEvery(int $seconds): Stream
    {
        return Stream::emits(asGenerator(function () use ($seconds) {
            $elapsed = 0;
            $prevTime = time();

            while (true) {
                sleep($seconds);

                $curTime = time();
                $elapsed += $curTime - $prevTime;
                $prevTime = $curTime;

                yield $elapsed;
            }
        }));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $const
     * @return Stream<TVI>
     */
    public static function constant(mixed $const): Stream
    {
        return Stream::emits(asGenerator(function () use ($const) {
            while (true) {
                yield $const;
            }
        }));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<Unit>
     */
    public static function infinite(): Stream
    {
        return Stream::constant(Unit::getInstance());
    }

    /**
     * {@inheritDoc}
     *
     * @param positive-int $by
     * @return Stream<int>
     */
    public static function range(int $start, int $stopExclusive, int $by = 1): Stream
    {
        return Stream::emits(asGenerator(function () use ($start, $stopExclusive, $by) {
            for ($i = $start; $i < $stopExclusive; $i += $by) {
                yield $i;
            }
        }));
    }

    /**
     * @template TKO
     * @template TVO
     *
     * @param Generator<TVO> $gen
     * @return Stream<TVO>
     */
    private function fork(Generator $gen): Stream
    {
        $this->forked = $this->forked
            ? throw new LogicException('multiple stream forks detected')
            : true;

        return Stream::emits($gen);
    }

    /**
     * @template TVO
     *
     * @param callable(TV): TVO $callback
     * @return Stream<TVO>
     */
    public function map(callable $callback): Stream
    {
        return $this->fork(Ops\MapOperation::of($this->emitter)(dropFirstArg($callback)));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $elem
     * @return Stream<TV|TVI>
     */
    public function appended(mixed $elem): Stream
    {
        return $this->fork(Ops\AppendedOperation::of($this->emitter)($elem));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param iterable<TVI> $suffix
     * @return Stream<TV|TVI>
     */
    public function appendedAll(iterable $suffix): Stream
    {
        return $this->fork(Ops\AppendedAllOperation::of($this->emitter)($suffix));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $elem
     * @return Stream<TV|TVI>
     */
    public function prepended(mixed $elem): Stream
    {
        return $this->fork(Ops\PrependedOperation::of($this->emitter)($elem));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param iterable<TVI> $prefix
     * @return Stream<TV|TVI>
     */
    public function prependedAll(iterable $prefix): Stream
    {
        return $this->fork(Ops\PrependedAllOperation::of($this->emitter)($prefix));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Stream<TV>
     */
    public function filter(callable $predicate): Stream
    {
        return $this->fork(Ops\FilterOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): Option<TVO> $callback
     * @return Stream<TVO>
     */
    public function filterMap(callable $callback): Stream
    {
        return $this->fork(Ops\FilterMapOperation::of($this->emitter)(dropFirstArg($callback)));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function filterNotNull(): Stream
    {
        return $this->fork(Ops\FilterNotNullOperation::of($this->emitter)());
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO> $fqcn
     * @param bool $invariant
     * @return Stream<TVO>
     */
    public function filterOf(string $fqcn, bool $invariant = false): Stream
    {
        return $this->fork(Ops\FilterOfOperation::of($this->emitter)($fqcn, $invariant));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param callable(TV): (iterable<TVO>) $callback
     * @return Stream<TVO>
     */
    public function flatMap(callable $callback): Stream
    {
        return $this->fork(Ops\FlatMapOperation::of($this->emitter)(dropFirstArg($callback)));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function tail(): Stream
    {
        return $this->fork(Ops\TailOperation::of($this->emitter)());
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Stream<TV>
     */
    public function takeWhile(callable $predicate): Stream
    {
        return $this->fork(Ops\TakeWhileOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Stream<TV>
     */
    public function dropWhile(callable $predicate): Stream
    {
        return $this->fork(Ops\DropWhileOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function take(int $length): Stream
    {
        return $this->fork(Ops\TakeOperation::of($this->emitter)($length));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function drop(int $length): Stream
    {
        return $this->fork(Ops\DropOperation::of($this->emitter)($length));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): void $callback
     * @return Stream<TV>
     */
    public function tap(callable $callback): Stream
    {
        return $this->fork(Ops\TapOperation::of($this->emitter)(dropFirstArg($callback)));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function repeat(): Stream
    {
        return $this->fork(Ops\RepeatOperation::of($this->emitter)());
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function repeatN(int $times): Stream
    {
        return $this->fork(Ops\RepeatNOperation::of($this->emitter)($times));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param TVI $separator
     * @return Stream<TV|TVI>
     */
    public function intersperse(mixed $separator): Stream
    {
        return $this->fork(Ops\IntersperseOperation::of($this->emitter)($separator));
    }

    /**
     * {@inheritDoc}
     *
     * @return Stream<TV>
     */
    public function lines(): Stream
    {
        return $this->fork(Ops\TapOperation::of($this->emitter)(function ($_key, $elem) {
            print_r($elem) . PHP_EOL;
        }));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param iterable<TVI> $that
     * @return Stream<TV|TVI>
     */
    public function interleave(iterable $that): Stream
    {
        return $this->fork(Ops\InterleaveOperation::of($this->emitter)($that));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVI
     *
     * @param iterable<TVI> $that
     * @return Stream<array{TV, TVI}>
     */
    public function zip(iterable $that): Stream
    {
        return $this->fork(Ops\ZipOperation::of($this->emitter)($that));
    }

    /**
     * {@inheritDoc}
     *
     * @param positive-int $size
     * @return Stream<Seq<TV>>
     */
    public function chunks(int $size): Stream
    {
        $chunks = Ops\ChunksOperation::of($this->emitter)($size);

        return $this->fork(
            Ops\MapOperation::of($chunks)(fn(mixed $_, array $chunk) => new ArrayList($chunk))
        );
    }

    /**
     * {@inheritDoc}
     *
     * @template D
     *
     * @param callable(TV): D $discriminator
     * @return Stream<array{D, Seq<TV>}>
     */
    public function groupAdjacentBy(callable $discriminator): Stream
    {
        $adjacent = Ops\GroupAdjacentByOperation::of($this->emitter)($discriminator);

        return $this->fork(Ops\MapOperation::of($adjacent)(function (mixed $_, array $pair) {
            $pair[1] = new ArrayList($pair[1]);
            return $pair;
        }));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV, TV): int $cmp
     * @return Stream<TV>
     */
    public function sorted(callable $cmp): Stream
    {
        return $this->fork(Ops\SortedOperation::of($this->emitter)($cmp));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     */
    public function every(callable $predicate): bool
    {
        return $this->leaf(Ops\EveryOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO> $fqcn
     * @param bool $invariant
     */
    public function everyOf(string $fqcn, bool $invariant = false): bool
    {
        return $this->leaf(Ops\EveryOfOperation::of($this->emitter)($fqcn, $invariant));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     */
    public function exists(callable $predicate): bool
    {
        return $this->leaf(Ops\ExistsOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO> $fqcn
     * @param bool $invariant
     */
    public function existsOf(string $fqcn, bool $invariant = false): bool
    {
        return $this->leaf(Ops\ExistsOfOperation::of($this->emitter)($fqcn, $invariant));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     *
     * @param callable(TV): TKO $callback
     * @return HashMap<TKO, TV>
     */
    public function reindex(callable $callback): HashMap
    {
        return $this->leaf(HashMap::collect(Ops\ReindexOperation::of($this->emitter)(dropFirstArg($callback))));
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Option<TV>
     */
    public function first(callable $predicate): Option
    {
        return $this->leaf(Ops\FirstOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @template TVO
     *
     * @param class-string<TVO> $fqcn
     * @param bool $invariant
     * @return Option<TVO>
     */
    public function firstOf(string $fqcn, bool $invariant = false): Option
    {
        return $this->leaf(Ops\FirstOfOperation::of($this->emitter)($fqcn, $invariant));
    }

    /**
     * {@inheritDoc}
     *
     * @template TA
     *
     * @param TA $init
     * @param callable(TA, TV): TA $callback
     * @return TA
     */
    public function fold(mixed $init, callable $callback): mixed
    {
        return $this->leaf(Ops\FoldOperation::of($this->emitter)($init, $callback));
    }

    /**
     * {@inheritDoc}
     *
     * @template TA
     *
     * @param callable(TV|TA, TV): (TV|TA) $callback
     * @return Option<TV|TA>
     */
    public function reduce(callable $callback): Option
    {
        return $this->leaf(Ops\ReduceOperation::of($this->emitter)($callback));
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<TV>
     */
    public function head(): Option
    {
        return $this->leaf(Ops\HeadOperation::of($this->emitter)());
    }

    /**
     * {@inheritDoc}
     *
     * @param callable(TV): bool $predicate
     * @return Option<TV>
     */
    public function last(callable $predicate): Option
    {
        return $this->leaf(Ops\LastOperation::of($this->emitter)(dropFirstArg($predicate)));
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<TV>
     */
    public function firstElement(): Option
    {
        return $this->leaf(Ops\FirstOperation::of($this->emitter)());
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<TV>
     */
    public function lastElement(): Option
    {
        return $this->leaf(Ops\LastOperation::of($this->emitter)());
    }

    /**
     * {@inheritDoc}
     */
    public function mkString(string $start = '', string $sep = ',', string $end = ''): string
    {
        return $this->leaf(Ops\MkStringOperation::of($this->emitter)($start, $sep, $end));
    }

    /**
     * {@inheritDoc}
     */
    public function drain(): void
    {
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        foreach ($this as $ignored) { }
    }

    /**
     * {@inheritDoc}
     *
     * @return list<TV>
     */
    public function toList(): array
    {
        return $this->leaf(asList($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<non-empty-list<TV>>
     */
    public function toNonEmptyList(): Option
    {
        return $this->leaf(asNonEmptyList($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is Stream<array{TKO, TVO}>
     *
     * @return array<TKO, TVO>
     */
    public function toArray(): array
    {
        return $this->leaf(asArray(
            asGenerator(function() {
                /** @var Generator<int, array{TKO, TVO}> $emitter */
                $emitter = $this->emitter;

                foreach ($emitter as [$key, $value]) {
                    yield $key => $value;
                }
            }),
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO of array-key
     * @template TVO
     * @psalm-if-this-is Stream<array{TKO, TVO}>
     *
     * @return Option<non-empty-array<TKO, TVO>>
     */
    public function toNonEmptyArray(): Option
    {
        return $this->leaf(asNonEmptyArray(
            asGenerator(function() {
                /** @var Generator<int, array{TKO, TVO}> $emitter */
                $emitter = $this->emitter;

                foreach ($emitter as [$key, $value]) {
                    yield $key => $value;
                }
            }),
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @return LinkedList<TV>
     */
    public function toLinkedList(): LinkedList
    {
        return $this->leaf(LinkedList::collect($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<NonEmptyLinkedList<TV>>
     */
    public function toNonEmptyLinkedList(): Option
    {
        return $this->leaf(NonEmptyLinkedList::collect($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayList<TV>
     */
    public function toArrayList(): ArrayList
    {
        return $this->leaf(ArrayList::collect($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<NonEmptyArrayList<TV>>
     */
    public function toNonEmptyArrayList(): Option
    {
        $arrayList = $this->leaf(ArrayList::collect($this->emitter));

        return Option::some($arrayList)
            ->filter(fn($list) => !$list->isEmpty())
            ->map(fn($list) => new NonEmptyArrayList($list));
    }

    /**
     * {@inheritDoc}
     *
     * @return HashSet<TV>
     */
    public function toHashSet(): HashSet
    {
        return $this->leaf(HashSet::collect($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @return Option<NonEmptyHashSet<TV>>
     */
    public function toNonEmptyHashSet(): Option
    {
        return $this->leaf(NonEmptyHashSet::collect($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     * @psalm-if-this-is Stream<array{TKO, TVO}>
     *
     * @return HashMap<TKO, TVO>
     */
    public function toHashMap(): HashMap
    {
        return $this->leaf(HashMap::collectPairs($this->emitter));
    }

    /**
     * {@inheritDoc}
     *
     * @template TKO
     * @template TVO
     * @psalm-if-this-is Stream<array{TKO, TVO}>
     *
     * @return Option<NonEmptyHashMap<TKO, TVO>>
     */
    public function toNonEmptyHashMap(): Option
    {
        return $this->leaf(NonEmptyHashMap::collectPairs($this->emitter));
    }

    /**
     * {@inheritDoc}
     */
    public function toFile(string $path, bool $append = false): void
    {
        $file = new SplFileObject($path, $append ? 'a' : 'w');

        foreach ($this as $elem) {
            $file->fwrite((string) $elem);
        }

        $file = null;
    }
}
