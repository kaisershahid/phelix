<?php
namespace DinoTech\StdLib\Collections\Traits;

use Consistence\Type\Type;
use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\DiffEntry;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\MapCollection;
use DinoTech\StdLib\KeyValue;

/**
 * @property $arr array
 */
trait CollectionTrait {
    private $preferredKeyValue = KeyValue::class;

    public function setKeyValueClass($class) : Collection {
        if (!is_subclass_of($class, KeyValue::class)) {
            throw new \InvalidArgumentException("$class must be subclass of " . KeyValue::class);
        }

        $this->preferredKeyValue = $class;
        return $this;
    }

    public function getNewKeyValue(string $key, $value) : KeyValue {
        $cls = $this->preferredKeyValue;
        return new $cls($key, $value);
    }

    public function keys() : array {
        return array_keys($this->arr);
    }

    public function values() : array {
        return array_values($this->arr);
    }

    public function isEmpty() : bool {
        return $this->count() == 0;
    }

    public function join(string $glue) : string {
        return implode($glue, $this->arr);
    }

    /**
     * @param callable $callback
     * @return static|Collection
     */
    public function traverse(callable $callback) : Collection {
        foreach ($this->arr as $idx => $ele) {
            $callback($this->getNewKeyValue($idx, $ele));
        }

        return $this;
    }

    public function reduce(callable $callback, $carry = null) {
        $result = $carry;
        foreach ($this->arr as $idx => $ele) {
            $result = $callback($this->getNewKeyValue($idx, $ele), $carry);
        }

        return $result;
    }

    public function findFirst($value) {
        $idx = array_search($value, $this->arr, true);
        return $idx === false ? null : $idx;
    }

    public function find($value) : array {
        $keys = [];
        foreach ($this->arr as $key => $ele) {
            if ($ele === $value) {
                $keys[]  = $key;
            }
        }

        return $keys;
    }

    public function removeFirst($value) {
        $key = $this->find($value);
        if ($key !== null) {
            $this->offsetUnset($key);
            return $value;
        }

        return null;
    }

    public function remove($value) : array {
        $rem = [];
        foreach ($this->find($value) as $key) {
            $this->offsetUnset($key);
            $rem[$key] = $value;
        }

        return $rem;
    }

    /**
     * @param $firstKey
     * @param $lastKey
     * @param int $max
     * @return static|Collection
     */
    public function slice($firstKey, $lastKey, int $max = 0) : Collection {
        $arr = ArrayUtils::slice($this->arr, $firstKey, $lastKey, $max);
        return new static($arr);
    }

    /**
     * @return array
     */
    public function jsonSerialize() {
        return $this->arr;
    }

    /**
     * @param $arr
     * @param bool $strict
     * @return DiffEntry[]|ListCollection
     * @throws \Consistence\InvalidArgumentTypeException
     */
    public function diff($arr, bool $strict = true) : ListCollection {
        $other = ArrayUtils::toArray($arr);
        return ArrayUtils::diff($this->arr, $other, $strict);
    }

    public function diffKeys($arr) : array {
        $other = ArrayUtils::toArray($arr);
        return array_diff_key($this->arr, $other);
    }

    public function union($arr, bool $strict = true) : MapCollection {
        $other = ArrayUtils::toArray($arr);
        return ArrayUtils::union($this->arr, $other, $strict);
    }

    public function unionKeys($arr) : array {
        $other = ArrayUtils::toArray($arr);
        return array_intersect_key($this->arr, $other);
    }
}
