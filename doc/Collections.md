# Collections
**Contents**
- [HashMap](#HashMap)
- [HashSet](#HashSet)
- [Hierarchy](#Hierarchy)
  - [empty collections](#empty-collections)
  - [non-empty collections](#non-empty-collections)
- [LinkedList](#LinkedList)
- [NonEmptyHashSet](#NonEmptyHashSet)
- [NonEmptyLinkedList](#NonEmptyLinkedList)

# HashMap

Standard `Map<TK, TV>` interface implementation.

Key-value storage. It's possible to store objects as keys.

Object keys comparison by default uses `spl_object_hash` function. If
you want to override default comparison behaviour then you need to
implement HashContract interface for your classes which objects will be
used as keys in HashMap.

``` php
use Fp\Collections\HashMap;

/**
 * @implements HashContract<Foo>
 */
class Foo implements HashContract
{
    public function __construct(public int $a)
    {
    }

    public function equals(mixed $rhs): bool
    {
        return $this->a === $rhs->a;
    }

    public function hashCode(): string
    {
        return implode(',', [md5((string) $this->a)]);
    }
}

$collection = HashMap::collect([
    [new Foo(1), 1], [new Foo(2), 2],
    [new Foo(3), 3], [new Foo(4), 4]
]);

[$reducedKeys, $reducedValues] = $collection
    ->map(fn($elem) => $elem + 10)
    ->filter(fn($elem) => $elem > 11)
    ->reindex(fn($elem, Foo $key) => $key->a)
    ->reduce(fn($acc, $elem) => [$acc[0] + $elem[0], $acc[1] + $elem[1]])
    ->getOrElse([0, 0]); // [9, 39]


$collection(new Foo(2))->getOrElse(0); // 2 

// It's possible to use new Foo(2) because Foo class implements HashContract
```

# HashSet

Standard `Set<TV>` interface implementation.

Collection of unique elements.

Object comparison by default uses `spl_object_hash` function. If you
want to override default comparison behaviour then you need to implement
HashContract interface for your classes which objects will be used as
elements in HashSet.

``` php
use Fp\Collections\HashSet;

/**
 * @implements HashContract<Foo>
 */
class Foo implements HashContract
{
    public function __construct(public int $a)
    {
    }

    public function equals(mixed $rhs): bool
    {
        return $this->a === $rhs->a;
    }

    public function hashCode(): string
    {
        return implode(',', [md5((string) $this->a)]);
    }
}

$collection = HashSet::collect([
    new Foo(1), new Foo(2), new Foo(2), 
    new Foo(3), new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->filter(fn(int $elem) => $elem > 1)
    ->reduce(fn($acc, $elem) => $acc + $elem)
    ->getOrElse(0); // 9

// Check if set contains given element 
$collection(new Foo(2)); // true
```

# Hierarchy

-   #### empty collections

        Collection<TV> -> Seq<TV> -> LinearSeq<TV> -> LinkedList<TV>

        Collection<TV> -> Seq<TV> -> IndexedSeq<TV> -> TODO

        Collection<TV> -> Set<TV> -> HashSet<TV>

        Collection<TV> -> Map<TK, TV> -> HashMap<TK, TV>

-   #### non-empty collections

        NonEmptyCollection<TV> -> NonEmptySeq<TV> -> NonEmptyLinearSeq<TV> -> NonEmptyLinkedList<TV>

        NonEmptyCollection<TV> -> NonEmptySeq<TV> -> NonEmptyIndexedSeq<TV> -> TODO

        NonEmptyCollection<TV> -> NonEmptySet<TV> -> NonEmptyHashSet<TV>

# LinkedList

Standard `Seq<TV>` interface implementation.

Collection with O(1) prepend operation.

``` php
use Tests\Mock\Foo;
use Fp\Collections\LinkedList;

$collection = LinkedList::collect([
    new Foo(1), new Foo(2) 
    new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->filter(fn(int $elem) => $elem > 1)
    ->reduce(fn($acc, $elem) => $acc + $elem)
    ->getOrElse(0); // 9
```

# NonEmptyHashSet

Standard `NonEmptySet<TV>` interface implementation.

Collection of unique elements.

Object comparison by default uses spl_object_hash function. If you want
to override default comparison behaviour then you need to implement
HashContract interface for your classes which objects will be used as
elements in HashSet.

``` php
use Fp\Collections\NonEmptyHashSet;

/**
 * @implements HashContract<Foo>
 */
class Foo implements HashContract
{
    public function __construct(public int $a)
    {
    }

    public function equals(mixed $rhs): bool
    {
        return $this->a === $rhs->a;
    }

    public function hashCode(): string
    {
        return implode(',', [md5((string) $this->a)]);
    }
}

$collection = NonEmptyHashSet::collect([
    new Foo(1), new Foo(2), new Foo(2), 
    new Foo(3), new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->reduce(fn($acc, $elem) => $acc + $elem); // 10
    
// Check if set contains given element 
$collection(new Foo(2)); // true
```

# NonEmptyLinkedList

Standard `NonEmptySeq<TV>` interface implementation.

Collection with O(1) prepend operation.

``` php
use Tests\Mock\Foo;
use Fp\Collections\NonEmptyLinkedList;

$collection = NonEmptyLinkedList::collect([
    new Foo(1), new Foo(2) 
    new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->reduce(fn($acc, $elem) => $acc + $elem); // 10
```
