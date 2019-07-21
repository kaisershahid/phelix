<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\KeyValue;

/**
 * @method Collection traverse(callable $callback)
 * @property array $arr
 */
trait MapAddAllTrait {
    /**
     * @param Collection $arr
     * @return static|Collection
     */
    public function addAll(Collection $arr) : Collection {
        $arr->traverse(function(KeyValue $kv) { $this[$kv->key()] = $kv->value(); });
        return $this;
    }

    /**
     * @param array $arr
     * @return static|Collection
     */
    public function arrayAddAll(array $arr) : Collection {
        $this->arr = array_merge($this->arr, $arr);
        return $this;
    }
}
