<?php
namespace DinoTech\StdLib\Collections;

use Consistence\Type\Type;
use DinoTech\StdLib\KeyValue;

class GenericList extends Collection {
    private $iterPos = 0;

    public function current() {
        return $this[$this->iterPos];
    }

    public function next() {
        $this->iterPos++;
    }

    public function key() {
        return $this->iterPos;
    }

    public function valid() {
        return $this->iterPos < count($this->arr);
    }

    public function rewind() {
        $this->iterPos = 0;
    }

    public function offsetGet($offset) {
        Type::checkType($offset, 'int');
        return $this->arr[$offset];
    }

    public function offsetSet($offset, $value) {
        Type::checkType($offset, 'int');
        $this->arr[$offset] = $value;
    }

    public function push($value) {
        $this->arr[] = $value;
        return $this;
    }

    public function pop() {
        return array_pop($this->arr);
    }

    public function shift() {
        return array_shift($this->arr);
    }

    public function unshift($val) {
        array_unshift($this->arr, $val);
        return $this;
    }

    public function addAll(Collection $arr) : Collection {
        if (is_array($arr)) {
            $this->arr = array_merge($this->arr, array_values($arr));
        } elseif ($arr instanceof Collection) {
            $arr->traverse(function(KeyValue $kv) { $this->push($kv->value()); });
        }
    }

    public function arrayAddAll(array $arr): Collection {
        $this->arr = array_merge($this->arr, array_values($arr));
        return $this;
    }

    public function map(callable $callback) : Collection {
        $arr = [];
        foreach ($this->arr as $key => $ele) {
            $arr[] = $callback(new KeyValue($key, $ele));
        }

        return new static($arr);
    }

    public function filter(callable $callback) : Collection {
        $arr = [];
        foreach ($this->arr as $key => $ele) {
            if ($callback(new KeyValue($key, $ele))) {
                $arr[] = $ele;
            }
        }

        return new static($arr);
    }

    public function reduce(callable $callback, $carry = null) {
        $result = $carry;
        foreach ($this->arr as $ele) {
            $result = $callback($ele, $carry);
        }

        return $result;
    }
}
