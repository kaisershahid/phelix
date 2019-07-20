<?php
namespace DinoTech\Phelix\Api\Service\Query;

class ParseTree {
    protected $parent;
    protected $value;
    /** @var ParseTree|QueryReference */
    protected $leafA;
    /** @var ParseTree|QueryReference */
    protected $leafB;

    public function __construct(ParseTree $parent = null) {
        $this->parent = $parent;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function setLeafA($node) {
        $this->leafA = $node;
        return $this;
    }

    public function setLeafB($node) {
        $this->leafB = $node;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValueOp() {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getLeafA() {
        return $this->leafA;
    }

    /**
     * @return mixed
     */
    public function getLeafB() {
        return $this->leafB;
    }

    public function getParent() : ?ParseTree {
        return $this->parent;
    }

    /**
     * @return ParseTree The newly lifted node
     */
    public function rotateLeft() : ParseTree {
        $top = $this->leafB;
        $topParent = $top->parent;
        $topLeafB = $top->leafB;

        $curParent = $this->parent;

        $top->leafA = $this;
        $top->parent = $curParent;
        $this->leafB = $topLeafB;
        $this->parent = $top;

        return $top;
    }
}
