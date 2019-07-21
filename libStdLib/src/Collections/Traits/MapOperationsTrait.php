<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\KeyValue;

/**
 * @property array $arr
 * @method KeyValue getNewKeyValue($key, $value)
 */
trait MapOperationsTrait {
    /**
     * @param callable $callback `function(KeyValue $kv) : {KeyValue|mixed}`
     * @return static|Collection
     */
    public function map(callable $callback): Collection {
        $arr = [];
        foreach ($this->arr as $key => $val) {
            $kv = $this->getNewKeyValue($key, $val);
            $mapped = $callback($kv);
            if ($mapped instanceof KeyValue) {
                $arr[$mapped->key()] = $mapped->value();
            } else {
                $arr[$key] = $mapped;
            }
        }

        return new static($arr);
    }

    /**
     * @param callable $callback `function(KeyValue $kv) : boolean`
     * @return static|Collection
     */
    public function filter(callable $callback): Collection {
        $arr = [];
        foreach ($this->arr as $key => $val) {
            if ($callback($this->getNewKeyValue($key, $val))) {
                $arr[$key] = $val;
            }
        }

        return new static($arr);
    }
}
