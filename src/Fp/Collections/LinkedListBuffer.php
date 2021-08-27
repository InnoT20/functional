<?php

declare(strict_types=1);

namespace Fp\Collections;

/**
 * Provides constant time append to list
 *
 * @internal
 * @template TV
 */
final class LinkedListBuffer
{
    /**
     * @var LinkedList<TV>
     */
    private LinkedList $first;

    /**
     * @var null|Cons<TV>
     */
    private ?Cons $last;

    private int $length;

    public function __construct()
    {
        $this->flush();
    }

    /**
     * @param TV $elem
     * @return self<TV>
     */
    public function append(mixed $elem): self
    {
        $cons = new Cons($elem, LinkedList::nil());
        $appended = new LinkedList($cons);

        if (0 === $this->length) {
            $this->first = $appended;
        } elseif (isset($this->last)) {
            /**
             * @dies-from-psalm-suppress
             * @psalm-suppress InaccessibleProperty
             */
            $this->last->tail = $appended;
        }

        $this->last = $cons;
        $this->length += 1;

        return $this;
    }

    /**
     * @return LinkedList<TV>
     */
    public function toLinkedList(): LinkedList
    {
        $first = $this->first;
        $this->flush();

        return $first;
    }

    private function flush(): void
    {
        $this->first = LinkedList::nil();
        $this->last = null;
        $this->length = 0;
    }
}
