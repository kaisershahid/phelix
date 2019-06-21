<?php
namespace DinoTech\StdLib\Collections;

/**
 * Base class for collection of values. Standardizes interface and expectations
 * of ordered lists (`List`) and maps (`Map`).
 *
 * For operations such as mapping and filtered, a `Collection` is always returned.
 *
 * If a value is an array, it remains untouched.
 *
 * @todo make $arr private and rework other methods
 * @todo add sort to interface
 * @todo add diff/union operations to interface
 * @todo move static functions to CollectionUtils?
 * @todo make a CollectionInterface
 */
abstract class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable {
    protected $arr = [];

    public function __construct(array $arr = []) {
        $this->arr = $arr;
    }

    public function offsetExists($offset) {
        return isset($this->arr[$offset]);
    }

    public function offsetGet($offset) {
        return $this->arr[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->arr[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->arr[$offset]);
    }

    public function count() {
        return count($this->arr);
    }

    public function isEmpty() {
        return $this->count() == 0;
    }

    public function jsonSerialize() {
        return $this->arr;
    }

    /**
     * Shallow traversal of items in index.
     * @param callable $callback `function($value, $offset)`
     * @return Collection
     */
    public function traverse(callable $callback) : Collection {
        foreach ($this->arr as $i => $v) {
            $callback($v, $i);
        }

        return $this;
    }

    public function join(string $glue) : string {
        return implode($glue, $this->arr);
    }

    /**
     * @param callable $callback `function(mixed $value)`
     * @return Collection
     */
    abstract public function map(callable $callback) : Collection;

    /**
     * @param callable $callback `function(mixed $value)`
     * @return Collection
     */
    abstract public function filter(callable $callback) : Collection;

    /**
     * @param callable $callback `function($value, $carry)`
     * @param mixed $carry
     * @return mixed
     */
    abstract public function reduce(callable $callback, $carry = null);

    public function keys() : array {
        return array_keys($this->arr);
    }

    public function values() : array {
        return array_values($this->arr);
    }

    /**
     * Adds input collection to current collection based on internal semantics.
     * **THIS IS UNSAFE WHEN DONE DURING ITERATION!**
     * @param Collection $arr
     * @return Collection
     */
    abstract function addAll(Collection $arr) : Collection;

    /**
     * Appends values from given array to collection. **THIS IS UNSAFE WHEN DONE
     * DURING ITERATION!**
     * @param array $arr
     * @return Collection
     */
    public function arrayAddAll(array $arr) : Collection {
        $this->arr = array_merge($this->arr, $arr);
        return $this;
    }

    /**
     * Returns key for first found value or null.
     * @param $value
     * @return int|string|null
     */
    public function findFirst($value) {
        $idx = array_search($value, $this->arr, true);
        return $idx === false ? null : $idx;
    }

    public function find($value) : array {
        return array_keys($this->arr, $value, true);
    }

    /**
     * Removes first occurrence of value.
     * @param $value
     * @return mixed The value removed
     */
    public function removeFirst($value) {
        $key = $this->find($value);
        if ($key !== null) {
            $this->offsetUnset($key);
            return $value;
        }

        return null;
    }

    /**
     * Remove all occurrences of value.
     * @param $value
     * @return array The intersection of removed key-values.
     */
    public function remove($value) : array {
        $rem = [];
        foreach ($this->removeAll($value) as $key) {
            $this->offsetUnset($key);
            $rem[$key] = $value;
        }

        return $rem;
    }

    /**
     * Gets all values between first key and last key, inclusive. If the first
     * key isn't found, an empty collection is returned. If the last key isn't
     * found, all key-values from the first key are returned.
     * @param int|string $firstKey
     * @param int|string $lastKey
     * @param int $max If > 0, up to that many key-values are returned.
     * @return Collection
     */
    public function slice($firstKey, $lastKey, $max = 0) : Collection {
        $arr = static::arraySlice($this->arr, $firstKey, $lastKey, $max);
        return new static($arr);
    }

    public static function arraySlice(array $arr, $firstKey, $lastKey, $max = 0) {
        $out = [];
        $count = 0;
        $found = false;
        foreach ($arr as $key => $val) {
            if ($key == $firstKey) {
                $found = true;
            }

            if ($found) {
                $out[$key] = $val;
                $count++;
            }

            if (($max > 0 && $max == $count) || $key == $lastKey) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param array|\ArrayAccess $arr
     * @param string $offset
     * @param mixed $default
     * @return mixed
     */
    public static function get($arr, string $offset, $default = null) {
        if (static::isSet($arr, $offset)) {
            return $arr[$offset];
        }

        return $default;
    }

    /**
     * Given a delimited key, attempt to find the longest matching key for the
     * given array, otherwise, check the first level of keys for remaining keys.
     * For instance, given `a.b.c.d`, check:
     *
     * - `a.b.c.d`
     * - `b.c.d` in `[a]`
     * - `c.d` in `[a][b]`
     * - `d` in `[a][b][c]`
     *
     * If expected key is not found at any level, the default is returned.
     *
     * @param array|\ArrayAccess $arr
     * @param string $key
     * @param string $separator
     * @param mixed $default
     * @return mixed
     */
    public static function getNested($arr, $key, $separator = '.', $default = null) {
        if ($key === null) {
            return $arr;
        } else if (static::isSet($arr, $key)) {
            return $arr[$key];
        }

        $splitKeys = explode($separator, $key, 2);
        $first = array_shift($splitKeys);
        $next = array_shift($splitKeys);
        if (static::isSet($arr, $first)) {
            return static::getNested($arr[$first], $next, $separator, $default);
        }

        return $default;
    }

    /**
     * @param array|\ArrayAccess $arr
     * @param string $key
     * @return bool
     */
    public static function isSet($arr, string $key) : bool {
        if (is_array($arr)) {
            return isset($arr[$key]);
        } else if ($arr instanceof \ArrayAccess) {
            return $arr->offsetExists($key);
        }

        return false;
    }
}
