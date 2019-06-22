<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\KeyValue;

/**
 * @property $arr array
 */
trait ListCollectionTrait {
    /**
     * @param mixed $val
     * @return static|ListCollection
     */
    public function push($value) : ListCollection {
        $this->arr[] = $value;
        return $this;
    }

    public function pop() {
        return array_pop($this->arr);
    }

    public function shift() {
        return array_shift($this->arr);
    }

    /**
     * @param mixed $val
     * @return static|ListCollection
     */
    public function unshift($val) : ListCollection {
        array_unshift($this->arr, $val);
        return $this;
    }

    /**
     * @param Collection $arr
     * @return static|Collection
     */
    public function addAll(Collection $arr) : Collection {
        if (is_array($arr)) {
            $this->arr = array_merge($this->arr, array_values($arr));
        } elseif ($arr instanceof Collection) {
            $arr->traverse(function(KeyValue $kv) { $this->push($kv->value()); });
        }

        return $this;
    }

    /**
     * @param array $arr
     * @return static|Collection
     */
    public function arrayAddAll(array $arr): Collection {
        $this->arr = array_merge($this->arr, array_values($arr));
        return $this;
    }

    /**
     * @param callable $callback
     * @return static|Collection
     */
    public function map(callable $callback) : Collection {
        $arr = [];
        foreach ($this->arr as $key => $ele) {
            $arr[] = $callback(new KeyValue($key, $ele));
        }

        return new static($arr);
    }

    /**
     * @param callable $callback
     * @return static|Collection
     */
    public function filter(callable $callback) : Collection {
        $arr = [];
        foreach ($this->arr as $key => $ele) {
            if ($callback(new KeyValue($key, $ele))) {
                $arr[] = $ele;
            }
        }

        return new static($arr);
    }
}
