# Collections
**Contents**
- [Hierarchy](#Hierarchy)
  - [empty collections](#empty-collections)
  - [non-empty collections](#non-empty-collections)
- [ArrayList](#ArrayList)
- [LinkedList](#LinkedList)
- [HashMap](#HashMap)
- [HashSet](#HashSet)
- [NonEmptyArrayList](#NonEmptyArrayList)
- [NonEmptyLinkedList](#NonEmptyLinkedList)
- [NonEmptyHashMap](#NonEmptyHashMap)
- [NonEmptyHashSet](#NonEmptyHashSet)

# Hierarchy

  - #### empty collections
    
        Collection<TV> -> Seq<TV> -> LinkedList<TV>
        
        Collection<TV> -> Seq<TV> -> ArrayList<TV>
        
        Collection<TV> -> Set<TV> -> HashSet<TV>
        
        Collection<TV> -> Map<TK, TV> -> HashMap<TK, TV>

  - #### non-empty collections
    
        NonEmptyCollection<TV> -> NonEmptySeq<TV> -> NonEmptyLinkedList<TV>
        
        NonEmptyCollection<TV> -> NonEmptySeq<TV> -> NonEmptyArrayList<TV>
        
        NonEmptyCollection<TV> -> NonEmptySet<TV> -> NonEmptyHashSet<TV>
        
        NonEmptyCollection<TV> -> NonEmptyMap<TK, TV> -> NonEmptyHashMap<TK, TV>

# ArrayList

`Seq<TV>` interface implementation.

Collection with O(1) `Seq::at()` and `Seq::__invoke()` operations.

``` php
$collection = ArrayList::collect([
    new Foo(1), new Foo(2) 
    new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->filter(fn(int $elem) => $elem > 1)
    ->reduce(fn($acc, $elem) => $acc + $elem)
    ->getOrElse(0); // 9
```

# LinkedList

`Seq<TV>` interface implementation.

Collection with O(1) prepend operation.

``` php
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

# HashMap

`Map<TK, TV>` interface implementation.

Key-value storage. It's possible to store objects as keys.

Object keys comparison by default uses `spl_object_hash` function. If
you want to override default comparison behaviour then you need to
implement HashContract interface for your classes which objects will be
used as keys in HashMap.

``` php
class Foo implements HashContract
{
    public function __construct(public int $a, public bool $b = true)
    {
    }

    public function equals(mixed $that): bool
    {
        return $that instanceof self
            && $this->a === $that->a
            && $this->b === $that->b;
    }

    public function hashCode(): string
    {
        return md5(implode(',', [$this->a, $this->b]));
    }
}

$collection = HashMap::collectPairs([
    [new Foo(1), 1], [new Foo(2), 2],
    [new Foo(3), 3], [new Foo(4), 4]
]);

$collection(new Foo(2))->getOrElse(0); // 2

$collection
    ->map(fn(int $value) => $value + 1)
    ->filter(fn(int $value) => $value > 2)
    ->fold(0, fn(int $acc, int $value) => $acc + $value); // 3+4+5=12 
```

# HashSet

`Set<TV>` interface implementation.

Collection of unique elements.

Object comparison by default uses `spl_object_hash` function. If you
want to override default comparison behaviour then you need to implement
HashContract interface for your classes which objects will be used as
elements in HashSet.

``` php
class Foo implements HashContract
{
    public function __construct(public int $a, public bool $b = true)
    {
    }

    public function equals(mixed $that): bool
    {
        return $that instanceof self
            && $this->a === $that->a
            && $this->b === $that->b;
    }

    public function hashCode(): string
    {
        return md5(implode(',', [$this->a, $this->b]));
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

/**
 * Check if set contains given element
 */ 
$collection(new Foo(2)); // true

/**
 * Check if one set is contained in another set 
 */
$collection->subsetOf(HashSet::collect([
    new Foo(1), new Foo(2), new Foo(3), 
    new Foo(4), new Foo(5), new Foo(6),
])); // true
```

  - Easy to move from MANY to ONE for many-to-one relations

<!-- end list -->

``` php
class Ceo
{
    public function __construct(public string $name) { }
}

class Manager
{
    public function __construct(public string $name, public Ceo $ceo) { }
}

class Developer
{
    public function __construct(public string $name, public Manager $manager) { }
}

$ceo = new Ceo('CEO');
$managerX = new Manager('Manager X', $ceo);
$managerY = new Manager('Manager Y', $ceo);
$developerA = new Developer('Developer A', $managerX);
$developerB = new Developer('Developer B', $managerX);
$developerC = new Developer('Developer C', $managerY);

HashSet::collect([$developerA, $developerB, $developerC])
    ->map(fn(Developer $developer) => $developer->manager)
    ->map(fn(Manager $manager) => $manager->ceo)
    ->tap(fn(Ceo $ceo) => print_r($ceo->name . PHP_EOL)); // CEO. Not CEOCEOCEO
```

# NonEmptyArrayList

`NonEmptySeq<TV>` interface implementation.

Collection with O(1) `NonEmptySeq::at()` and `NonEmptySeq::__invoke()`
operations.

``` php
$collection = NonEmptyArrayList::collect([
    new Foo(1), new Foo(2) 
    new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->reduce(fn($acc, $elem) => $acc + $elem); // 10
```

# NonEmptyLinkedList

`NonEmptySeq<TV>` interface implementation.

Collection with O(1) prepend operation.

``` php
$collection = NonEmptyLinkedList::collect([
    new Foo(1), new Foo(2) 
    new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->reduce(fn($acc, $elem) => $acc + $elem); // 10
```

# NonEmptyHashMap

`NonEmptyMap<TK, TV>` interface implementation.

Key-value storage. It's possible to store objects as keys.

Object keys comparison by default uses `spl_object_hash` function. If
you want to override default comparison behaviour then you need to
implement HashContract interface for your classes which objects will be
used as keys in HashMap.

``` php
class Foo implements HashContract
{
    public function __construct(public int $a, public bool $b = true)
    {
    }

    public function equals(mixed $that): bool
    {
        return $that instanceof self
            && $this->a === $that->a
            && $this->b === $that->b;
    }

    public function hashCode(): string
    {
        return md5(implode(',', [$this->a, $this->b]));
    }
}

$collection = NonEmptyHashMap::collectPairsNonEmpty([
    [new Foo(1), 1], [new Foo(2), 2],
    [new Foo(3), 3], [new Foo(4), 4]
]);

$collection(new Foo(2))->getOrElse(0); // 2

$collection
    ->map(fn(int $value) => $value + 1)
    ->toList(); // [[1, 2], [2, 3], [3, 4], [4, 5]]
```

# NonEmptyHashSet

`NonEmptySet<TV>` interface implementation.

Collection of unique elements.

Object comparison by default uses spl\_object\_hash function. If you
want to override default comparison behaviour then you need to implement
HashContract interface for your classes which objects will be used as
elements in HashSet.

``` php
class Foo implements HashContract
{
    public function __construct(public int $a, public bool $b = true)
    {
    }

    public function equals(mixed $that): bool
    {
        return $that instanceof self
            && $this->a === $that->a
            && $this->b === $that->b;
    }

    public function hashCode(): string
    {
        return md5(implode(',', [$this->a, $this->b]));
    }
}

$collection = NonEmptyHashSet::collect([
    new Foo(1), new Foo(2), new Foo(2), 
    new Foo(3), new Foo(3), new Foo(4),
]);

$collection
    ->map(fn(Foo $elem) => $elem->a)
    ->reduce(fn($acc, $elem) => $acc + $elem); // 10
    
/**
 * Check if set contains given element 
 */
$collection(new Foo(2)); // true

/**
 * Check if one set is contained in another set 
 */
$collection->subsetOf(NonEmptyHashSet::collect([
    new Foo(1), new Foo(2), new Foo(3), 
    new Foo(4), new Foo(5), new Foo(6),
])); // true
```
