# ArrayList

```IndexedSeq<TV>``` interface implementation.

Collection with O(1) ```Seq::at()``` and ```IndexedSeq::__invoke()``` operations.

```php
use Tests\Mock\Foo;
use Fp\Collections\ArrayList;

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

