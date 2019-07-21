<?php
namespace DinoTech\StdLib\Collections;

use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;
use DinoTech\StdLib\Collections\Traits\ListCollectionTrait;
use DinoTech\StdLib\Collections\Traits\ListOperationsTrait;

/**
 * A set that stores unique values by type (e.g. `5` and `"5"` are treated as two
 * unique values).
 */
class StandardSet implements SetCollection {
    // @todo move find/remove to FindRemoveTrait
    use CollectionTrait {
        remove as _remove;
        removeFirst as _removeFirst;
        find as _find;
        findFirst as _findFirst;
    }
    use ListOperationsTrait;
    use CountableTrait;
    use IteratorTrait;

    private $arr = [];
    private $valueHashToIndex = [];
    private $lastIdx = -1;

    protected function getHashForValue($value) {
        $key = null;
        if (is_scalar($value)) {
            $key = $value;
            if (is_string($value) &&  strlen($value) > 32) {
                $key = md5($value);
            } else {
                $key = gettype($value) . ':' . $value;
            }
        } else if (is_array($value)) {
            $key = md5(json_encode($value));
        } else {
            $key = spl_object_hash($value);
        }

        return $key;
    }

    public function clear(): Collection {
        $this->arr = [];
        $this->clearIterator();
    }

    public function add($value) : SetCollection {
        $key = $this->getHashForValue($value);
        if (!isset($this->valueHashToIndex[$key])) {
            $this->arr[] = $value;
            $this->lastIdx++;
            $this->valueHashToIndex[$key] = $this->lastIdx;
        }

        return $this;
    }

    public function remove($value) : array {
        $key = $this->getHashForValue($value);
        if (isset($this->valueHashToIndex[$key])) {
            $idx = $this->valueHashToIndex[$key];
            $val = $this->arr[$idx];
            unset($this->arr[$idx]);
            unset($this->valueHashToIndex[$key]);
            return [$idx => $val];
        }

        return [];
    }

    public function removeFirst($value) {
        $rem = $this->remove($value);
        return array_pop($rem);
    }

    public function find($value) : array {
        $key = $this->getHashForValue($value);
        if (isset($this->valueHashToIndex[$key])) {
            $idx = $this->valueHashToIndex[$key];
            $val = $this->arr[$idx];
            return [$idx => $val];
        }

        return [];
    }

    public function findFirst($value) {
        $find = $this->find($value);
        return array_pop($find);
    }

    public function offsetSet($offset, $value) {
        $this->add($value);
    }

    public function offsetUnset($offset) {
        if (isset($this->arr[$offset])) {
            $key = $this->getHashForValue($this->arr[$offset]);
            unset($this->arr[$offset]);
            unset($this->valueHashToIndex[$key]);
            // @todo reset $arr with array_values()?
        }
    }

    public function offsetExists($offset) {
        return isset($this->arr[$offset]);
    }

    public function offsetGet($offset) {
        return ArrayUtils::get($this->arr, $offset);
    }

    public function addAll(Collection $arr): Collection {
        $this->arrayAddAll($arr->values());
        return $this;
    }

    public function arrayAddAll(array $arr): Collection {
        foreach ($arr as $val) {
            $this->offsetSet(null, $val);
        }

        return $this;
    }
}
