<?php

declare(strict_types=1);

namespace Fp\Streams;

use Fp\Collections\ArrayList;
use Fp\Collections\Seq;
use Fp\Functional\Option\Option;
use Generator;
use LogicException;

use function Fp\Callable\asGenerator;
use function Fp\of;

/**
 * @psalm-immutable
 * @template-covariant TV
 * @psalm-require-implements StreamChainableOps
 * @psalm-require-implements StreamEmitter
 */
trait StreamChainable
{
    /**
     * @psalm-readonly-allow-private-mutation $forked
     */
    private bool $forked = false;

    /**
     * @psalm-template TVO
     * @psalm-param Generator<TVO> $gen
     * @psalm-return self<TVO>
     */
    private function fork(Generator $gen): self
    {
        $this->forked = !$this->forked
            ? $this->forked = true
            : throw new LogicException('multiple stream forks detected');

        return self::emits($gen);
    }

    /**
     * @template TVO
     * @psalm-param callable(TV): TVO $callback
     * @psalm-return self<TVO>
     */
    public function map(callable $callback): self
    {
        return $this->fork(asGenerator(function () use ($callback) {
            foreach ($this as $elem) {
                /** @var TV $e */
                $e = $elem;

                yield $callback($e);
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVI
     * @psalm-param TVI $elem
     * @psalm-return self<TV|TVI>
     */
    public function appended(mixed $elem): self
    {
        return $this->fork(asGenerator(function () use ($elem) {
            foreach ($this as $prefixElem) {
                yield $prefixElem;
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
        return $this->fork(asGenerator(function() use ($suffix) {
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
        return $this->fork(asGenerator(function () use ($elem) {
            yield $elem;

            foreach ($this as $prefixElem) {
                yield $prefixElem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVI
     * @psalm-param iterable<TVI> $prefix
     * @psalm-return self<TV|TVI>
     */
    public function prependedAll(iterable $prefix): self
    {
        return $this->fork(asGenerator(function() use ($prefix) {
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
        return $this->fork(asGenerator(function () use ($predicate) {
            foreach ($this as $element) {
                /** @var TV $e */
                $e = $element;

                if ($predicate($e)) {
                    yield $e;
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
        return $this->fork(asGenerator(function () use ($callback) {
            foreach ($this as $element) {
                /** @var TV $e */
                $e = $element;
                $result = $callback($e);

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
        return $this->fork(asGenerator(function () use ($callback) {
            foreach ($this as $element) {
                /** @var TV $e */
                $e = $element;
                $result = $callback($e);

                foreach ($result as $item) {
                    yield $item;
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function tail(): self
    {
        return $this->fork(asGenerator(function () {
            $isFirst = true;

            foreach ($this as $elem) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                yield $elem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-param callable(TV): bool $predicate
     * @psalm-return self<TV>
     */
    public function takeWhile(callable $predicate): self
    {
        return $this->fork(asGenerator(function () use ($predicate) {
            foreach ($this as $elem) {
                /** @var TV $e */
                $e = $elem;

                if (!$predicate($e)) {
                    break;
                }

                yield $e;
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
        return $this->fork(asGenerator(function () use ($predicate) {
            $toggle = true;

            foreach ($this as $elem) {
                /** @var TV $e */
                $e = $elem;

                if (!($toggle = $toggle && $predicate($e))) {
                    yield $e;
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function take(int $length): self
    {
        return $this->fork(asGenerator(function () use ($length) {
            $i = 0;

            foreach ($this as $elem) {
                if ($i === $length) {
                    break;
                }

                yield $elem;
                $i++;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function drop(int $length): self
    {
        return $this->fork(asGenerator(function () use ($length) {
            $i = 0;

            foreach ($this as $elem) {
                if ($i < $length) {
                    $i++;
                    continue;
                }

                yield $elem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @param callable(TV): void $callback
     * @psalm-return self<TV>
     */
    public function tap(callable $callback): self
    {
        return $this->fork(asGenerator(function () use ($callback) {
            foreach ($this as $elem) {
                /** @var TV $e */
                $e = $elem;
                $callback($e);
                yield $elem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @return self<TV>
     */
    public function repeat(): self
    {
        return $this->fork(asGenerator(function () {
            /** @var Seq<TV> $buffer */
            $buffer = ArrayList::collect($this);

            foreach ($buffer as $elem) {
                yield $elem;
            }

            while(true) {
                foreach ($buffer as $elem) {
                    yield $elem;
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @return self<TV>
     */
    public function repeatN(int $times): self
    {
        return $this->fork(asGenerator(function () use ($times) {
            /** @var Seq<TV> $buffer */
            $buffer = ArrayList::collect($this);

            foreach ($buffer as $elem) {
                yield $elem;
            }

            for($i = 0; $i < $times - 1; $i++) {
                foreach ($buffer as $elem) {
                    yield $elem;
                }
            }
        }));
    }

    /**
     * @inheritDoc
     * @template TVI
     * @param TVI $separator
     * @psalm-return self<TV|TVI>
     */
    public function intersperse(mixed $separator): self
    {
        return $this->fork(asGenerator(function () use ($separator) {
            $isFirst = true;

            foreach ($this as $elem) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    yield $separator;
                }

                yield $elem;
            }
        }));
    }

    /**
     * @inheritDoc
     * @psalm-return self<TV>
     */
    public function lines(): self
    {
        return $this->tap(function ($elem) {
            print_r($elem) . PHP_EOL;
        });
    }

    /**
     * @inheritDoc
     * @template TVI
     * @param iterable<TVI> $that
     * @return self<TV|TVI>
     */
    public function interleave(iterable $that): self
    {
        return $this
            ->zip($that)
            ->flatMap(fn(array $pair) => self::emits($pair));
    }

    /**
     * @inheritDoc
     * @template TVI
     * @param iterable<TVI> $that
     * @return self<array{TV, TVI}>
     */
    public function zip(iterable $that): self
    {
        return $this->fork(asGenerator(function () use ($that) {
            $thisIter = asGenerator(fn() => $this);
            $thatIter = asGenerator(fn() => $that);

            $thisIter->rewind();
            $thatIter->rewind();

            while ($thisIter->valid() && $thatIter->valid()) {
                $thisElem = $thisIter->current();
                $thatElem = $thatIter->current();

                yield [$thisElem, $thatElem];

                $thisIter->next();
                $thatIter->next();
            }
        }));
    }

    /**
     * @inheritDoc
     * @return self<Seq<TV>>
     */
    public function chunks(int $size): self
    {
        return $this->fork(asGenerator(function () use ($size) {
            $chunk = [];
            $i = 0;

            foreach ($this as $elem) {
                $i++;
                $chunk[] = $elem;

                if (0 === $i % $size) {
                    yield new ArrayList($chunk);
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                yield new ArrayList($chunk);
            }
        }));
    }

    /**
     * @inheritDoc
     * @template D
     * @param callable(TV): D $discriminator
     * @return Stream<array{D, Seq<TV>}>
     */
    public function groupAdjacentBy(callable $discriminator): Stream
    {
        return $this->fork(asGenerator(function () use ($discriminator) {
            $buffer = [];
            $prevDisc = null;
            $isHead = true;

            foreach ($this as $elem) {
                /** @var TV $e */
                $e = $elem;

                if ($isHead) {
                    $isHead = false;
                    $prevDisc = $discriminator($e);
                }

                $curDisc = $discriminator($e);

                if ($prevDisc !== $curDisc) {
                    yield [$prevDisc, new ArrayList($buffer)];
                    $buffer = [];
                }

                $buffer[] = $elem;
                $prevDisc = $curDisc;
            }

            if (!empty($buffer)) {
                yield [$prevDisc, new ArrayList($buffer)];
            }
        }));
    }
}

