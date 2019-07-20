<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\PredicateInterface;
use DinoTech\Phelix\Expressions\ReferenceInterface;

/**
 * Represents a node in NTree.
 */
class NTreeNode implements \JsonSerializable {
    /** @var ReferenceInterface|PredicateInterface|NTree */
    protected $value;
    /** @var int */
    protected $type;

    public function __construct($value, int $type) {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return ReferenceInterface|PredicateInterface|NTree
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

    public function isOperator() : bool {
        return ($this->type & NTree::OP) === NTree::OP;
    }

    public function isValue() : bool {
        return ($this->type & NTree::VAL) === NTree::VAL;
    }

    public function jsonSerialize() {
        return [
            'value' => $this->value instanceof \JsonSerializable ? $this->value->jsonSerialize() : $this->value,
            'type' => $this->type
        ];
    }

    public function __toString() {
        return (string) $this->value;
    }
}
