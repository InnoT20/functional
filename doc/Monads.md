# Monads
**Contents**
- [Option monad](#Option-monad)
- [Either monad](#Either-monad)
- [Do-notation](#Do-notation)
- [Examples](#Examples)
  - [Filter chaining](#Filter-chaining)

# Option monad

Represents optional computation.

Consists of Some and None subclasses.

Prevents null pointer exceptions and allow short-circuiting the
computation if there was step which returned None.

``` php
/** 
 * @return Option<User> 
 */
function getUserById(int $id): Option {
  /** 
   * @var User|null $user 
   */
  $user = $db->getUser($id);
  
  return Option::fromNullable($user);
}

/** 
 * @return Option<Order> 
 */
function getUserFirstOrder(User $user): Option {
  /** 
   * @var Order|null $order 
   */
  $order = $user->getOrders()[0] ?? null;
  
  return Option::fromNullable($order);
}


/** 
 * @return Option<TrackNumber> 
 */
function getOrderTrackNumber(Order $order): Option {
  /** 
   * @var TrackNumber|null $order 
   */
  $trackNumber = $order->getTracknumber();
  
  return Option::fromNullable($trackNumber);
}

/** 
 * @return Option<string> 
 */
function getTrackingStatus(TrackingNumber $trackingNumber): Option {
  /** 
   * @var string|null $order 
   */
  $status = $trackingNumber->getLastTrackingStatus();
  
  return Option::fromNullable($status);
}

/** @var string $status */
$status = getUserById(654)
    ->flatMap(fn(User $user) => getUserFirstOrder($user))
    ->flatMap(fn(Order $order) => getOrderTrackNumber($order))
    ->flatMap(fn(TrackingNumber $number) => getTrackingStatus($number))
    ->getOrElse('no status info');
```

# Either monad

Represents computation with possible errors.

Consists of Left and Right subclasses. Left represents error outcome and
Right represents successful outcome.

Allow short-circuiting the computation if there was step which returned
Left (error outcome).

``` php
/** 
 * @return Either<string, User> 
 */
function getUserById(int $id): Either {
  /** 
   * @var User|null $user 
   */
  $user = $db->getUser($id);
  
  return isset($user)
    ? Either::right($user)
    : Either::left('User not found!');
}

/** 
 * @return Either<string, Order> 
 */
function getUserFirstOrder(User $user): Either {
  /** 
   * @var Order|null $order 
   */
  $order = $user->getOrders()[0] ?? null;
  
  return isset($order)
    ? Either::right($order)
    : Either::left('Order not found!');
}


/** 
 * @return Either<string, TrackNumber> 
 */
function getOrderTrackNumber(Order $order): Either {
  /** 
   * @var TrackNumber|null $order 
   */
  $trackNumber = $order->getTracknumber();
  
  return isset($trackNumber)
    ? Either::right($trackNumber)
    : Either::left('No track number yet. But will be after 30 seconds');
}

/** 
 * @return Either<string, string> 
 */
function getTrackingStatus(TrackingNumber $trackingNumber): Either {
  /** 
   * @var string|null $order 
   */
  $status = $trackingNumber->getLastTrackingStatus();
  
  return isset($status)
    ? Either::right($status)
    : Either::left('Unable to parse track current status');
}

/** @var string $statusOrErrorMessage */
$statusOrErrorMessage = getUserById(654)
    ->flatMap(fn(User $user) => getUserFirstOrder($user))
    ->flatMap(fn(Order $order) => getOrderTrackNumber($order))
    ->flatMap(fn(TrackingNumber $number) => getTrackingStatus($number))
    ->get();
```

# Do-notation

In case of long computation chain you can use do notation to shorten
amount of code. Do-notation is just syntax-sugar.

``` php
/** 
 * @return Option<User> 
 */
function getUserById(int $id): Option {}

/** 
 * @return Option<Order> 
 */
function getUserFirstOrder(User $user): Option {}


/** 
 * @return Option<TrackNumber> 
 */
function getOrderTrackNumber(Order $order): Option {}

/** 
 * @return Option<string> 
 */
function getTrackingStatus(TrackingNumber $trackingNumber): Option {}

/** 
 * @var string $status 
 */
$status = Option::do(function () {
    $user = yield getUserById(654);
    $order = yield getUserFirstOrder($user);
    $trackNumber = yield getOrderTrackNumber($order);
    return yield getTrackingStatus($trackNumber);
})->getOrElse('no status info');
```

# Examples

-   #### Filter chaining

    ``` php
        /**
         * @psalm-return Option<Union>
         */
        function getUnionTypeParam(Union $union): Option
        {
            return Option::do(function () use ($union) {
                $atomics = $union->getAtomicTypes();
                yield proveTrue(1 === count($atomics));
                $atomic = yield head($atomics);

                return yield self::filterTIterableValueTypeParam($atomic)
                    ->orElse(fn() => self::filterTArrayValueTypeParam($atomic))
                    ->orElse(fn() => self::filterTListValueTypeParam($atomic))
                    ->orElse(fn() => self::filterTGenericObjectValueTypeParam($atomic))
                    ->orElse(fn() => self::filterTKeyedArrayValueTypeParam($atomic));
            });
        }

        /**
         * @psalm-return Option<Union>
         */
        function filterTIterableTypeParam(Atomic $atomic): Option
        {
            return Option::some($atomic)
                ->filter(fn(Atomic $a) => $a instanceof TIterable)
                ->map(fn(TIterable $a) => $a->type_params[1]);
        }

        /**
         * @psalm-return Option<Union>
         */
        function filterTArrayTypeParam(Atomic $atomic): Option
        {
            return Option::some($atomic)
                ->filter(fn(Atomic $a) => $a instanceof TArray)
                ->map(fn(TArray $a) => $a->type_params[1]);
        }

        /**
         * @psalm-return Option<Union>
         */
        function filterTListTypeParam(Atomic $atomic): Option
        {
            return Option::some($atomic)
                ->filter(fn(Atomic $a) => $a instanceof TList)
                ->map(fn(TList $a) => $a->type_param);
        }

        /**
         * @psalm-return Option<Union>
         */
        function filterTKeyedArrayTypeParam(Atomic $atomic): Option
        {
            return Option::some($atomic)
                ->filter(fn(Atomic $a) => $a instanceof TKeyedArray)
                ->map(fn(TKeyedArray $a) => $a->getGenericValueType());
        }

        /**
         * @psalm-return Option<Union>
         */
        function filterTGenericObjectTypeParam(Atomic $atomic): Option
        {
            return Option::some($atomic)
                ->filter(fn(Atomic $a) => $a instanceof TGenericObject)
                ->flatMap(fn(TGenericObject $a) => Option::fromNullable(match (true) {
                    is_a($a->value, Seq::class, true) => $a->type_params[0],
                    is_a($a->value, Set::class, true) => $a->type_params[0],
                    is_a($a->value, Map::class, true) => $a->type_params[1],
                    is_a($a->value, NonEmptySeq::class, true) => $a->type_params[0],
                    is_a($a->value, NonEmptySet::class, true) => $a->type_params[0],
                    default => null
                }));
        }
    ```
