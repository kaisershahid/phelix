<?php
namespace DinoTech\StdLib\Collections;

/**
 * Holds difference of a key between source value and target value.
 */
class DiffEntry implements \JsonSerializable {
    /** @var string|int */
    private $key;
    /** @var mixed */
    private $sourceValue;
    /** @var mixed */
    private $targetValue;

    public function __construct($key, $sourceValue, $targetValue) {
        $this->key = $key;
        $this->sourceValue = $sourceValue;
        $this->targetValue = $targetValue;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getSourceValue() {
        return $this->sourceValue;
    }

    /**
     * @return mixed
     */
    public function getTargetValue() {
        return $this->targetValue;
    }

    public function jsonSerialize() {
        return [
            'key' => $this->key,
            'sourceValue' => $this->sourceValue,
            'targetValue' => $this->targetValue
        ];
    }
}
