<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\KeyValue;

/**
 * @property array $arr
 * @method KeyValue getNewKeyValue($key, $value)
 */
trait ListOperationsTrait {
    /**
     * @param callable $callback
     * @return static|Collection
     */
    public function map(callable $callback) : Collection {
        $arr = [];
        foreach ($this->arr as $key => $ele) {
            $arr[] = $callback($this->getNewKeyValue($key, $ele));
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
            if ($callback($this->getNewKeyValue($key, $ele))) {
                $arr[] = $ele;
            }
        }

        return new static($arr);
    }
}
