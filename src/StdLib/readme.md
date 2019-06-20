# Standard Library `StdLib`



## Collections

Taking a cue from Java, the `Collection` class defines a standard interface for all types of collections as well as global convenience methods. A sequential array is implemented with a `IndexedList`, and key-value pairs are implemented with `Map`.

Throughout most of the service framework, these two objects will be used in place of a standard `array`.

### Method Conventions

1. If a method explicitly accepts an `array`, its method name is prefixed with `array` (e.g. `Collection::arraySlice()`)
2. For callbacks, a `KeyValue` instance is given instead of just the value. This removes the confusion of any contexts where both key and value are supplied.
    1. return values can also be `KeyValue`. in contexts where it's possible to remap a value to a different key, the key on the object is used. Specific method documentation will call out this case.
