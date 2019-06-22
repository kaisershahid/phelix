<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\Collection;

class BaseConfig implements Collection {
    public function traverse(callable $callback): Collection {
        // TODO: Implement traverse() method.
    }

    public function join(string $glue): string {
        // TODO: Implement join() method.
    }

    public function map(callable $callback): Collection {
        // TODO: Implement map() method.
    }

    public function filter(callable $callback): Collection {
        // TODO: Implement filter() method.
    }

    public function reduce(callable $callback, $carry = null) {
        // TODO: Implement reduce() method.
    }

    public function keys(): array {
        // TODO: Implement keys() method.
    }

    public function values(): array {
        // TODO: Implement values() method.
    }

    function addAll(Collection $arr): Collection {
        // TODO: Implement addAll() method.
    }

    public function arrayAddAll(array $arr): Collection {
        // TODO: Implement arrayAddAll() method.
    }

    public function findFirst($value) {
        // TODO: Implement findFirst() method.
    }

    public function find($value): array {
        // TODO: Implement find() method.
    }

    public function removeFirst($value) {
        // TODO: Implement removeFirst() method.
    }

    public function remove($value): array {
        // TODO: Implement remove() method.
    }

    public function slice($firstKey, $lastKey, $max = 0): Collection {
        // TODO: Implement slice() method.
    }

    public function current() {
        // TODO: Implement current() method.
    }

    public function next() {
        // TODO: Implement next() method.
    }

    public function key() {
        // TODO: Implement key() method.
    }

    public function valid() {
        // TODO: Implement valid() method.
    }

    public function rewind() {
        // TODO: Implement rewind() method.
    }

    public function offsetExists($offset) {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset) {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value) {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset) {
        // TODO: Implement offsetUnset() method.
    }

    public function count() {
        // TODO: Implement count() method.
    }

}
