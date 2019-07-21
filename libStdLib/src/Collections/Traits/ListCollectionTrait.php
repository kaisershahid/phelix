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
}
