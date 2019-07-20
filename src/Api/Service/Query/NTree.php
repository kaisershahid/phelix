<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\StdLib\Comparator;

/**
 * A tree that virtually models an N-tree as operators for nodes and values/other
 * trees for leaves. Allows sequential pushing of alternating values and operators
 * and automatic operator precedence rebalancing.
 *
 * @todo use custom exception
 */
class NTree implements Comparator, \JsonSerializable {
    const VAL = 2;
    const OP = 1;
    const OP_UNARY = 4;

    protected $parent;
    /** @var NTreeNode[] */
    protected $nodes = [];
    protected $last = 0;
    protected $lastOp;
    protected $isGrouped = false;
    /** @var Comparator */
    protected $opComparator;

    public function __construct(NTree $parent = null) {
        $this->parent = $parent;
        $this->opComparator = $this;
    }

    public function compare($first, $second): int {
        if ($first > $second) {
            return 1;
        } else if ($first < $second) {
            return -1;
        }

        return 0;
    }

    public function getParent() : ?NTree {
        return $this->parent;
    }

    public function setGrouped(bool $grouped) : NTree {
        $this->isGrouped = $grouped;
        return $this;
    }

    public function isGrouped() : bool {
        return $this->isGrouped;
    }

    public function setComparator(Comparator $comparator) : NTree {
        $this->opComparator = $comparator;
        return $this;
    }

    public function pushOperator($op) : NTree {
        // @todo support unary operator
        // @todo make a OperatorAdjacency interface with validAdjacency($left, $right)
        if ($this->last === self::OP) {
            throw new \Exception("can't push operator after another operator");
        }

        if ($this->isRightHigher($op)) {
            return $this->pushDownRight($op);
        } else if ($this->isLeftHigher($op)) {
            return $this->pushDownLeft($op);
        } else {
            $this->nodes[] = new NTreeNode($op, self::OP);
            $this->last    = self::OP;
            $this->lastOp  = $op;
            return $this;
        }
    }

    protected function isRightHigher(string $op) : bool {
        return $this->lastOp !== null && $this->opComparator->compare($this->lastOp, $op) == -1;
    }

    protected function isLeftHigher(string $op) : bool {
        return $this->lastOp !== null && $this->opComparator->compare($this->lastOp, $op) == 1;
    }

    /**
     * ```
     * [A, lastOp, B] ->
     * [A, lastOp, v]
     *         [B, op, ...]
     * ```
     * @param string $op
     * @return NTree
     * @throws \Exception
     */
    protected function pushDownRight(string $op) {
        $lastVal = $this->popLast();
        $tree = (new NTree($this))
            ->setComparator($this->opComparator)
            ->pushValue($lastVal->getValue())
            ->pushOperator($op);
        $this->pushValue($tree);
        return $tree;
    }

    /**
     * ```
     * [X, op1, A, lastOp, B] ->
     * [X, op1, v, op, ...]
     *   [A, lastOp, B]
     * ```
     * @param string $op
     * @return NTree
     * @throws \Exception
     */
    protected function pushDownLeft(string $op) {
        $leftSubTree = $this->snipLeft(2);
        $leftSubTree->parent = $this;
        $this->pushValue($leftSubTree)->pushOperator($op);
        return $this;
    }

    public function popLast() : ?NTreeNode {
        $last = array_pop($this->nodes);
        if ($last) {
            $resetLast = $last->isValue() ? self::OP : self::VAL;
            if ($this->nodes) {
                $this->last = $resetLast;
            } else {
                $this->last = null;
            }

            // set lastOp to null, then walk left until first operator is found
            // -- reset lastOp to that
            if ($last->isOperator()) {
                $this->lastOp = $this->findClosestOperator();
            }
        }

        return $last;
    }

    public function findClosestOperator() : ?string {
        $right = count($this->nodes) - 1;
        while ($right >= 0) {
            $node = $this->nodes[$right];
            if ($node->isOperator()) {
                return $node->getValue();
            }

            $right--;
        }

        return null;
    }

    public function getLastOp() {
        return $this->lastOp;
    }

    /**
     * Removes values from the left, including operators between values, until
     * the limit is hit. The snipped values are then put back into a new tree.
     * @param int $limit
     * @return NTree
     */
    public function snipLeft(int $limit = 1) : NTree {
        $nodes = [];
        $valc = 0;
        $last = $this->popLast();
        while ($last) {
            $nodes[] = $last;
            if ($last->isValue()) {
                $valc++;
            }

            if ($valc === $limit) {
                break;
            }

            $last = $this->popLast();
        }

        /** @var NTreeNode[] $nodes */
        $nodes = array_reverse($nodes);
        $tree = new NTree();
        foreach ($nodes as $node) {
            if ($node->isValue()) {
                $tree->pushValue($node->getValue());
            } else {
                $tree = $tree->pushOperator($node->getValue());
            }
        }

        return $tree;
    }

    public function pushValue($val) : NTree {
        if ($this->last === self::VAL) {
            throw new \Exception("can't push value after another value");
        }

        $this->nodes[] = new NTreeNode($val, self::VAL);
        $this->last    = self::VAL;
        return $this;
    }

    /**
     * Generates a grouped NTree and pushes itself as a value of this tree, then
     * returns the new tree.
     * @return NTree
     * @throws \Exception
     */
    public function pushGroupedSubtree() : NTree {
        $tree = (new NTree($this))
            ->setGrouped(true)->setComparator($this->opComparator);
        $this->pushValue($tree);
        return $tree;
    }

    /**
     * @return NTreeNode[]
     */
    public function getNodes() : array {
        return $this->nodes;
    }

    public function wasLastOperator() : bool {
        return $this->last === self::OP;
    }

    public function wasLastValue() : bool {
        return $this->last === self::VAL;
    }

    public function jsonSerialize() {
        $arr = [];
        foreach ($this->nodes as $node) {
            $arr[] = $node->jsonSerialize();
        }

        return $arr;
    }

    public function __toString() {
        $buff = [];
        $term = '';
        if ($this->isGrouped) {
            $buff[] = '(';
            $term = ')';
        }

        $sub = [];
        foreach ($this->nodes as $node) {
            $sub[] = (string) $node;
        }

        $buff[] = implode(' ', $sub);
        $buff[] = $term;
        return implode('', $buff);
    }
}
