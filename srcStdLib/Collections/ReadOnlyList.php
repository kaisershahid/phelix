<?php
namespace DinoTech\StdLib\Collections;

class ReadOnlyList extends StandardList {
    public function offsetUnset($offset) {
        throw new UnsupportedOperationException("not allowed");
    }

    public function removeFirst($value) {
        throw new UnsupportedOperationException("not allowed");
    }

    public function remove($value): array {
        throw new UnsupportedOperationException("not allowed");
    }

    public function rewind() {
        throw new UnsupportedOperationException("not allowed");
    }

    public function push($value) : ListCollection {
        throw new UnsupportedOperationException("not allowed");
    }

    public function pop() {
        throw new UnsupportedOperationException("not allowed");
    }

    public function shift() {
        throw new UnsupportedOperationException("not allowed");
    }

    public function unshift($val) : ListCollection {
        throw new UnsupportedOperationException("not allowed");
    }

    public function addAll(Collection $arr): Collection {
        throw new UnsupportedOperationException("not allowed");
    }

    public function arrayAddAll(array $arr): Collection {
        throw new UnsupportedOperationException("not allowed");
    }

}
