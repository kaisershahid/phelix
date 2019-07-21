<?php
namespace DinoTech\StdLib\Collections\Traits;

/**
 * @property $arr array
 */
trait ArrayAccessTrait {
    public function offsetExists($offset) {
        return isset($this->arr[$offset]);
    }

    public function offsetGet($offset) {
        return $this->arr[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->arr[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->arr[$offset]);
    }
}
