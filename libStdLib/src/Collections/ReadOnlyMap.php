<?php
namespace DinoTech\StdLib\Collections;

use DinoTech\StdLib\Collections\UnsupportedOperationException;

class ReadOnlyMap extends StandardMap {
    public function offsetUnset($offset) {
        throw new UnsupportedOperationException("not allowed");
    }

    public function arrayAddAll(array $arr): Collection {
        throw new UnsupportedOperationException("not allowed");
    }

    public function removeFirst($value) {
        throw new UnsupportedOperationException("not allowed");
    }

    public function remove($value): array {
        throw new UnsupportedOperationException("not allowed");
    }

    public function addAll(Collection $arr): Collection {
        throw new UnsupportedOperationException("not allowed");
    }

    public function pluckKeys(string ...$keys): MapCollection {
        throw new UnsupportedOperationException("not allowed");
    }
}
