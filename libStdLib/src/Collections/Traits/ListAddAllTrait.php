<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\Collection;

/**
 * @property array $arr
 */
trait ListAddAllTrait {
    /**
     * @param Collection $arr
     * @return static|Collection
     */
    public function addAll(Collection $arr) : Collection {
        $this->arrayAddAll($arr->values());
        return $this;
    }

    /**
     * @param array $arr
     * @return static|Collection
     * @todo move to AddAll trait
     */
    public function arrayAddAll(array $arr): Collection {
        $this->arr = array_merge($this->arr, array_values($arr));
        return $this;
    }
}
