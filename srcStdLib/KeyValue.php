<?php
namespace DinoTech\StdLib;

/**
 * Unifies passing/receiving key-value pairs in a consistent way.
 * @todo make interface
 * @todo move to Collections
 */
class KeyValue {
    private $key;
    private $value;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    public function key() {
        return $this->key;
    }

    public function value() {
        return $this->value;
    }

    /**
     * To play nice with implode.
     * @return string
     */
    public function __toString() {
        return (string) $this->value;
    }
}
