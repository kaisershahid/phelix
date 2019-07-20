<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\PredicateFactory;
use DinoTech\Phelix\Expressions\PredicateInterface;
use DinoTech\Phelix\Expressions\Predicates\SingleValuePredicate;
use DinoTech\Phelix\Expressions\ReferenceInterface;

class NTreePredicateBuilder {
    private $root;
    private $curRoot = [];
    private $curRootIndex = 0;

    public function __construct(NTree $tree) {
        $this->root = $tree;
        $this->curRoot = [$tree];
    }

    protected function getCurRoot() : NTree {
        return $this->curRoot[$this->curRootIndex];
    }

    /**
     * Successively build a predicate tree from right to left. E.g.
     *
     * Input: `[a, op1, b, op2, c]`
     * Reverse(Input): `[c, op2, b, op1, a]`
     * Pass1:
     *  right := c
     *  op := op2
     *  left := b
     *  lastPred := Predicate(op, left, right) # now the 'right'
     * Pass2 (repeat until complete):
     *  op := op1
     *  left := a
     *  lastPred := Predicate(op, left, lastPred)
     *
     * This ultimately creates the binary tree in correct left-to-right evaluation order:
     *
     *    op1
     *  /    \
     * a     op2
     *     /    \
     *    b      .
     *        /   .
     *      .    opN
     *          /   \
     *         y     z
     */

    /** @var NTree|NTreeNode|PredicateInterface|ReferenceInterface */
    protected $lexR; // right
    /** @var NTreeNode */
    protected $lexO; // operator
    /** @var NTree|NTreeNode|PredicateInterface|ReferenceInterface */
    protected $lexL; // left
    protected $lexemesReverse;

    public function getPredicate() : PredicateInterface {
        $this->reset();
        $this->shiftOrSetRight();
        $this->shiftOperator();
        $this->shiftOrSetLeft();

        if ($this->left() === null) {
            return (new SingleValuePredicate($this->right()))
                ->setPrefixOp($this->operator());
        }

        $this->reduceRight();
        while ($this->consumeNext()) {
            $this->reduceRight();
        }

        return $this->lexR;
    }

    protected function reset() {
        $this->lexR = $this->lexO = $this->lexR = null;
        $this->lexemesReverse = array_reverse($this->getCurRoot()->getNodes());
    }

    protected function shift() {
        return array_shift($this->lexemesReverse);
    }

    protected function shiftOrSetLeft($val = null) : NTreePredicateBuilder {
        if ($val !== null) {
            $this->lexL = $val;
            return $this;
        }

        $this->lexL = $this->shift();
        return $this;
    }

    protected function shiftOperator() : NTreePredicateBuilder {
        $this->lexO = $this->shift();
        return $this;
    }

    protected function shiftOrSetRight($val = null) : NTreePredicateBuilder {
        if ($val !== null) {
            $this->lexR = $val;
            return $this;
        }

        $this->lexR = $this->shift();
        return $this;
    }

    public function left() {
        if ($this->lexL instanceof NTreeNode) {
            return $this->lexL->getValue();
        }

        return $this->lexL;
    }

    public function operator() {
        if ($this->lexO instanceof NTreeNode) {
            return $this->lexO->getValue();
        }

        return $this->lexO;
    }

    public function right() {
        if ($this->lexR instanceof NTreeNode) {
            return $this->lexR->getValue();
        }

        return $this->lexR;
    }

    protected function reduceRight() {
        $l = $this->left();
        if (($l instanceof NTree)) {
            $l = (new static($l))->getPredicate();
        }

        $r = $this->right();
        if (($r instanceof NTree)) {
            $r = (new static($r))->getPredicate();
        }

        $op = $this->operator();
        $this->lexR = PredicateFactory::fromOperator($op, $l, $r);
    }

    protected function consumeNext() : bool {
        $this->shiftOperator()->shiftOrSetLeft();
        return $this->lexO !== null;
    }
}
