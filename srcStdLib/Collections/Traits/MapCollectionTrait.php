<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\MapCollection;
use DinoTech\StdLib\KeyValue;

/**
 * @property $arr array
 */
trait MapCollectionTrait {
    /**
     * Removes keys from current map and places them into new map.
     * @param string ...$keys
     * @return static|MapCollection
     */
    public function pluckKeys(string ...$keys) : MapCollection {
        $arr = [];
        foreach ($keys as $key) {
            $arr[$key] = ArrayUtils::get($this->arr, $key);
            unset($this->arr[$key]);
        }

        return new static($arr);
    }
}
