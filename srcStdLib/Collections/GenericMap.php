<?php
namespace DinoTech\StdLib\Collections;

use DinoTech\StdLib\KeyValue;

class GenericMap extends Collection {
    private $keys;
    private $curKey;
    private $iterPos;

    public function current() {
        if ($this->keys == null) {
            throw new \RuntimeException('must call rewind() first');
        }

        return $this->arr[$this->curKey];
    }

    public function next() {
        $this->iterPos++;
        $this->curKey = $this->keys[$this->iterPos];
    }

    public function key() {
        return $this->curKey;
    }

    public function valid() {
        return $this->iterPos < count($this->keys);
    }

    public function rewind() {
        $this->keys = array_keys($this->arr);
        $this->iterPos = 0;
        $this->curKey = $this->keys[$this->iterPos];
    }

    /**
     * Maps current values with given callback. Callback is given a `KeyValue`
     * instance as the first parameter and can return either a new `KeyValue` or
     * some other value. If `KeyValue` is returned, the key from that is used,
     * otherwise, the current key is used.
     *
     * @param callable $callback `function(KeyValue $kv) : {KeyValue|mixed}`
     * @return Collection
     */
    public function map(callable $callback): Collection {
        $arr = [];
        foreach ($this->arr as $key => $val) {
            $kv = new KeyValue($key, $val);
            $mapped = $callback($kv);
            if ($mapped instanceof KeyValue) {
                $arr[$mapped->key()] = $mapped->value();
            } else {
                $arr[$key] = $mapped;
            }
        }

        return new GenericMap($arr);
    }

    /**
     * @param callable $callback `function(KeyValue $kv) : boolean`
     * @return Collection
     */
    public function filter(callable $callback): Collection {
        $arr = [];
        foreach ($this->arr as $key => $val) {
            if ($callback(new KeyValue($key, $val))) {
                $arr[$key] = $val;
            }
        }

        return new GenericMap($arr);
    }

    /**
     * @param callable $callback `function(KeyValue $kv, mixed $carry)`
     * @param mixed $carry
     * @return mixed
     */
    public function reduce(callable $callback, $carry = null) {
        $result = $carry;
        foreach ($this->arr as $key => $val) {
            $result = $callback(new KeyValue($key, $val), $result);
        }

        return $result;
    }

    public function addAll(Collection $arr) : Collection {
        $arr->traverse(function(KeyValue $kv) { $this[$kv->key()] = $kv->value(); });
    }
}
