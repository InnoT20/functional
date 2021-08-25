# Functional PHP
PHP Functional Programming library. Monads and common use functions.

![psalm level](https://shepherd.dev/github/whsv26/functional/level.svg)
![psalm type coverage](https://shepherd.dev/github/whsv26/functional/coverage.svg)
[![phpunit coverage](https://coveralls.io/repos/github/whsv26/functional/badge.svg)](https://coveralls.io/github/whsv26/functional)

## Documentation
- ### [Collections](doc/Collections.md)
- ### [Functions](doc/Functions.md)
- ### [Monads](doc/Monads.md)


## Installation

### Composer 

```console
$ composer require whsv26/functional
```

### Enable psalm plugin (optional)
To improve type inference for particular functions

```console
$ vendor/bin/psalm-plugin enable Fp\\Psalm\\FunctionalPlugin
```


## Examples

- Type safety
```php
/**
 * Inferred type is NonEmptyLinkedList<1|2|3>
 */
$collection = NonEmptyLinkedList::collectNonEmpty([1, 2, 3]);

/**
 * Inferred type is NonEmptyLinkedList<int>
 * 
 * Literal types are dropped after map transformation,
 * but NonEmpty collection prefix has been kept
 */
$mappedCollection = $collection->map(fn($elem) => $elem - 1);

/**
 * Inferred type is LinkedList<positive-int>
 * NonEmpty prefix has been dropped
 */
$filteredCollection = $mappedCollection->filter(fn(int $elem) => $elem > 0);
```
```php
$source = [new Foo(1), null, new Bar(2)];

/**
 * Inferred type is ArrayList<Foo|Bar>
 * Null type was removed
 * NonEmpty prefix was removed
 */
$withoutNulls = NonEmptyArrayList::collectNonEmpty($source)->filterNotNull();

/**
 * Inferred type is ArrayList<Foo>
 * Bar type was removed
 */
$onlyFoos = $withoutNulls->filter(fn($elem) => $elem instanceof Foo);

```

- Covariance
```php
class User {}
class Admin extends User {}

/**
* @param NonEmptyCollection<User> $collection
*/
function acceptUsers(NonEmptyCollection $collection): void {}

/** 
 * @var NonEmptyLinkedList<Admin> $collection 
 */
$collection = NonEmptyLinkedList::collectNonEmpty([new Admin()]);

/**
 * You can pass collection of admins instead of users
 * Because of covariant template parameter
 */
acceptUsers($collection);
```

- Immutability
```php
$originalCollection = LinkedList::collect([1, 2, 3]);

/**
 * $originalCollection won't be changed
 */
$prependedCollection = $originalCollection->prepended(0);

/**
 * $prependedCollection won't be changed
 */
$mappedCollection = $prependedCollection->map(fn(int $elem) => $elem + 1);
```

- Null safety
```php
/**
 * @var Collection<int> $emptyCollection 
 */
$emptyCollection = getEmptyCollection();

$resultWithDefaultValue = $emptyCollection
    ->reduce(fn(int $accumulator, int $element) => $accumulator + $element)
    ->getOrElse(0);

/**
 * @return Option<float>
 */
function div(int $a, int $b): Option {
    return 0 === $b 
        ? Option::none()
        : Option::some($a / $b)
}

/**
 * It's possible there is no first collection element above zero
 * In this case the execution will short circuit (stop) 
 * And no Null Pointer Exception will be thrown
 */
$emptyCollection
    ->first(fn(int $elem) => $elem > 0)
    ->map(fn(int $elem) => $elem + 1)
    ->flatMap(fn(int $elem): float => div($elem, $elem - 1))
    ->getOrElse(0)
```


## Contribution

### Build documentation

1) Install dependencies
  ```console
  whsv26@whsv26:~$ sudo apt install pandoc
  ```

2) Generate **doc** from **src**
  ```console
  whsv26@whsv26:~$ make
  ```
