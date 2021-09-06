<?php

declare(strict_types=1);

namespace Fp\Collections;

use Generator;
use Iterator;
use Fp\Functional\Option\Option;

use function Fp\of;

/**
 * O(1) {@see Seq::prepended} operation
 *
 * @psalm-immutable
 * @template-covariant TV
 * @extends AbstractLinearSeq<TV>
 */
abstract class LinkedList extends AbstractLinearSeq
{
    /**
     * @inheritDoc
     * @psalm-pure
     * @template TVI
     * @param array<TVI>|Collection<TVI>|NonEmptyCollection<TVI>|PureIterable<TVI> $source
     * @return self<TVI>
     */
    public static function collect(array|Collection|NonEmptyCollection|PureIterable $source): self
    {
        return PureIterable::of(fn() => $source)->toLinkedList();
    }

    /**
     * @inheritDoc
     * @return Iterator<int, TV>
     */
    public function getIterator(): Iterator
    {
        return new LinkedListIterator($this);
    }

    /**
     * @inheritDoc
     * @return LinkedList<TV>
     */
    public function toLinkedList(): LinkedList
    {
        return $this;
    }

    /**
     * @inheritDoc
     * @template TVI
     * @psalm-param TVI $elem
     * @psalm-return self<TV|TVI>
     */
    public function appended(mixed $elem): self
    {
        return self::collect(PureIterable::of(function () use ($elem) {
            foreach ($this as $item) {
                yield $item;
            }

            yield $elem;
        }));
    }

    /**
     * @inheritDoc
     * @template TVI
     * @psalm-param iterable<TVI> $suffix
     * @psalm-return self<TV|TVI>
     */
    public function appendedAll(iterable $suffix): self
    {
        return self::collect(PureIterable::of(function() use ($suffix) {
            foreach ($this as $prefixElem) {
                yield $prefixElem;
            }

            foreach ($suffix as $suffixElem) {
                yield $suffixElem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVI
     * @psalm-param TVI $elem
     * @psalm-return self<TV|TVI>
     */
    public function prepended(mixed $elem): self
    {
        return new Cons($elem, $this);
    }

    /**
     * @inheritDoc
     * @template TVI
     * @psalm-param iterable<TVI> $prefix
     * @psalm-return self<TV|TVI>
     */
    public function prependedAll(iterable $prefix): self
    {
        return self::collect(PureIterable::of(function() use ($prefix) {
            foreach ($prefix as $prefixElem) {
                yield $prefixElem;
            }

            foreach ($this as $suffixElem) {
                yield $suffixElem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-param callable(TV): bool $predicate
     * @psalm-return self<TV>
     */
    public function filter(callable $predicate): self
    {
        return self::collect(PureIterable::of(function () use ($predicate) {
            foreach ($this as $element) {
                if ($predicate($element)) {
                    yield $element;
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-template TVO
     * @psalm-param callable(TV): Option<TVO> $callback
     * @psalm-return self<TVO>
     */
    public function filterMap(callable $callback): self
    {
        return self::collect(PureIterable::of(function () use ($callback) {
            foreach ($this as $element) {
                $result = $callback($element);

                if ($result->isSome()) {
                    yield $result->get();
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function filterNotNull(): self
    {
        return $this->filter(fn(mixed $v) => !is_null($v));
    }

    /**
     * @inheritDoc
     * @psalm-template TVO
     * @psalm-param class-string<TVO> $fqcn fully qualified class name
     * @psalm-param bool $invariant if turned on then subclasses are not allowed
     * @psalm-return self<TVO>
     */
    public function filterOf(string $fqcn, bool $invariant = false): self
    {
        /** @var self<TVO> */
        return $this->filter(fn(mixed $v): bool => of($v, $fqcn, $invariant));
    }

    /**
     * @inheritDoc
     * @psalm-template TVO
     * @psalm-param callable(TV): iterable<TVO> $callback
     * @psalm-return self<TVO>
     */
    public function flatMap(callable $callback): self
    {
        return self::collect(PureIterable::of(function () use ($callback) {
            foreach ($this as $element) {
                $result = $callback($element);

                foreach ($result as $item) {
                    yield $item;
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVO
     * @psalm-param callable(TV): TVO $callback
     * @psalm-return self<TVO>
     */
    public function map(callable $callback): self
    {
        return self::collect(PureIterable::of(function () use ($callback) {
            foreach ($this as $element) {
                yield $callback($element);
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function reverse(): self
    {
        $list = Nil::getInstance();

        foreach ($this as $elem) {
            $list = $list->prepended($elem);
        }

        return $list;
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function tail(): self
    {
        return match (true) {
            $this instanceof Cons => $this->tail,
            $this instanceof Nil => $this,
        };
    }

    /**
     * @inheritDoc
     * @experimental
     * @psalm-param callable(TV): (int|string) $callback returns element unique id
     * @psalm-return self<TV>
     */
    public function unique(callable $callback): self
    {
        $pairs = $this->map(function($elem) use ($callback) {
            /** @var TV $elem */
            return [$callback($elem), $elem];
        });

        return self::collect(HashMap::collect($pairs)->values());
    }

    /**
     * @inheritDoc
     * @psalm-param callable(TV): bool $predicate
     * @psalm-return self<TV>
     */
    public function takeWhile(callable $predicate): self
    {
        return self::collect(PureIterable::of(function () use ($predicate) {
            foreach ($this as $element) {
                if (!$predicate($element)) {
                    break;
                }

                yield $element;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-param callable(TV): bool $predicate
     * @psalm-return self<TV>
     */
    public function dropWhile(callable $predicate): self
    {
        return self::collect(PureIterable::of(function () use ($predicate) {
            foreach ($this as $element) {
                if ($predicate($element)) {
                    continue;
                }

                yield $element;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function take(int $length): self
    {
        return self::collect(PureIterable::of(function () use ($length) {
            foreach ($this as $i => $element) {
                if ($i === $length) {
                    break;
                }

                yield $element;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function drop(int $length): self
    {
        return self::collect(PureIterable::of(function () use ($length) {
            foreach ($this as $i => $element) {
                if ($i < $length) {
                    continue;
                }

                yield $element;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-param callable(TV, TV): int $cmp
     * @psalm-return self<TV>
     */
    public function sorted(callable $cmp): self
    {
        $sorted = $this->toArray();

        /** @psalm-suppress ImpureFunctionCall */
        usort($sorted, $cmp);

        return self::collect($sorted);
    }
}
