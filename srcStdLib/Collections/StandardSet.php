<?php
namespace DinoTech\StdLib\Collections;

class StandardSet extends StandardList {
    public function offsetSet($offset, $value) {
        if ($this->findFirst($value) !== null) {
            parent::push($value);
        }
    }

    // @todo addAll
    // @todo arrayAddAll
}
