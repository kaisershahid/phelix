<?php
namespace DinoTech\StdLib\Collections\Traits;

/**
 * @property $arr array
 */
trait CountableTrait {
    public function count() {
        return count($this->arr);
    }
}
