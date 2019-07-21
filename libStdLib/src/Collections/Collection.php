<?php
namespace DinoTech\StdLib\Collections;

use DinoTech\StdLib\Collections\Traits\MapCollectionTrait;
use DinoTech\StdLib\KeyValue;

/**
 * Unifies all basic and modern collection operations into a single interface
 * with the intent of increased developer productivity.
 *
 * All operations supporting a callback must accept `KeyValue` as the first parameter.
 *
 * All operations can throw `UnsupportedOperationException`
 *
 * @todo add sort to interface
 * @todo make IndexedCollection interface to contain key-type operations (and make as parent of List/Map)
 */
interface Collection extends \ArrayAccess, \Iterator, \Countable, \JsonSerializable {
    /**
     * Sets a subclass of `KeyValue` to use.
     * @param string $class
     * @return Collection
     * @throws \InvalidArgumentException
     * @todo remove this from interface
     */
    public function setKeyValueClass($class) : Collection;

    public function getNewKeyValue(string $key, $value) : KeyValue;

    /**
     * Returns all defined keys/indices.
     * @return array
     * @throws UnsupportedOperationException
     */
    public function keys() : array;

    /**
     * Returns all values.
     * @return array
     * @throws UnsupportedOperationException
     */
    public function values() : array;

    /**
     * Checks if collection is empty.
     * @return bool
     * @throws UnsupportedOperationException
     */
    public function isEmpty() : bool;

    /**
     * Joins all values by a given string.
     * @param string $glue
     * @return string
     * @throws UnsupportedOperationException
     */
    public function join(string $glue) : string;

    /**
     * Shallow traversal of items in index.
     * @param callable $callback `function(KeyValue $kv, $offset)`
     * @return Collection
     * @throws UnsupportedOperationException
     */
    public function traverse(callable $callback) : Collection;

    /**
     * Maps all key-values by a callback to a new collection.
     * @param callable $callback `function(mixed $value)`
     * @return Collection
     * @throws UnsupportedOperationException
     */
    public function map(callable $callback) : Collection;

    /**
     * Filters all key-values by a callback to a new collection.
     * @param callable $callback `function(mixed $value)`
     * @return Collection
     * @throws UnsupportedOperationException
     */
    public function filter(callable $callback) : Collection;

    /**
     * Reduces all key-values by a callback.
     * @param callable $callback `function($value, $carry)`
     * @param mixed $carry
     * @return mixed
     * @throws UnsupportedOperationException
     */
    public function reduce(callable $callback, $carry = null);

    /**
     * Adds input collection to current collection based on internal semantics --
     * lists will append values to the end, maps will merge key-values.
     *
     * **THIS IS UNSAFE WHEN DONE DURING ITERATION!**
     * @param Collection $arr
     * @return Collection
     * @throws UnsupportedOperationException
     */
    function addAll(Collection $arr) : Collection;

    /**
     * Appends values from given array to collection.
     *
     * **THIS IS UNSAFE WHEN DONE
     * DURING ITERATION!**
     * @param array $arr
     * @return Collection
     * @throws UnsupportedOperationException
     */
    public function arrayAddAll(array $arr) : Collection;

    /**
     * Returns key for first found value or null.
     * @param $value
     * @return int|string|null
     * @throws UnsupportedOperationException
     */
    public function findFirst($value);

    /**
     * Returns all keys for all found values.
     * @param $value
     * @return array
     * @throws UnsupportedOperationException
     */
    public function find($value) : array;

    /**
     * Removes first occurrence of value.
     * @param $value
     * @return mixed The value removed
     * @throws UnsupportedOperationException
     */
    public function removeFirst($value);

    /**
     * Remove all occurrences of value.
     * @param $value
     * @return array The intersection of removed key-values.
     * @throws UnsupportedOperationException
     */
    public function remove($value) : array;

    /**
     * Gets all values between first key and last key, inclusive. If the first
     * key isn't found, an empty collection is returned. If the last key isn't
     * found, all key-values from the first key are returned.
     * @param int|string $firstKey
     * @param int|string $lastKey
     * @param int $max If > 0, up to that many key-values are returned.
     * @return Collection
     * @throws UnsupportedOperationException
     */
    public function slice($firstKey, $lastKey, int $max = 0) : Collection;

    /**
     * Clears the internal collection.
     * @return Collection
     * @throws UnsupportedOperationException
     */
    public function clear() : Collection;

    /**
     * Compares values by key and returns the diff.
     * @param array|Collection $other
     * @return DiffEntry[]|ListCollection
     * @throws UnsupportedOperationException
     * @todo redefine this to only do diff by value
     */
    public function diff($other, bool $strict = true) : ListCollection;

    /**
     * Returns set of keys in current collection not in other collection.
     * @param array|Collection $other
     * @return array
     * @todo move to MapCollection
     */
    public function diffKeys($other) : array;

    /**
     * Compares values by key and returns the union.
     * @param array|Collection $other
     * @param bool $strict
     * @return Collection
     * @todo redefine this to only do union by value and change to Collection
     */
    public function union($other, bool $strict = true) : MapCollection;

    /**
     * Returns set of keys in both current and other collection.
     * @param array|Collection $other
     * @return array
     * @todo move to MapCollection
     */
    public function unionKeys($other) : array;
}
