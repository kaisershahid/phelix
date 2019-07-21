<?php
namespace DinoTech\StdLib\Collections;

class ReadOnlyCollection extends StandardCollection {
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
}
