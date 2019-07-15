<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\OperatorPrecedence;
use DinoTech\Phelix\Expressions\PredicateFactory;
use DinoTech\Phelix\Expressions\PredicateInterface;
use DinoTech\Phelix\Expressions\Predicates\BranchedPredicate;

/**
 * Builds a binary tree representing the expression. The node itself is the operator,
 * with subtrees to its left or right (terminating in a reference). All push operations
 * return a `StatementBuilder`, which allows the current builder to manage its own
 * state and remove need for a stack -- this greatly reduces parser complexity.
 */
class StatementBuilder implements \JsonSerializable {
    /** @var StatementBuilder */
    protected $parent;
    /** @var bool */
    private $inEscape = false;
    /** @var string */
    private $inQuote;
    /** @var bool */
    protected $isGrouped = false;

    /** @var QueryReference|StatementBuilder */
    protected $leafA;
    protected $op;
    /** @var QueryReference|StatementBuilder */
    protected $leafB;
    /** @var QueryReference */
    private $curRef;

    public function __construct(StatementBuilder $parent = null) {
        $this->parent = $parent;
    }

    public function getParent() : ?StatementBuilder {
        return $this->parent;
    }

    /**
     * Places the current reference on either the left or right branch. If left
     * branch is already set and no operator was seen, throws an exception.
     * @throws \Exception
     */
    protected function setRefOnPredicateBranch() {
        if ($this->curRef !== null) {
            if ($this->leafA === null) {
                $this->leafA = $this->curRef;
            } else if ($this->leafB === null) {
                $this->leafB = $this->curRef;
            } else {
                throw new \Exception("dangling @{$this->curRef}@");
            }

            $this->curRef = null;
        }
    }

    protected function setLeafA($label) : StatementBuilder {
        $this->leafA = $label;
        return $this;
    }

    protected function setLeftFromBuff() {
        if ($this->leafA === null) {
            if ($this->curRef !== null) {
                $this->leafA  = $this->curRef;
                $this->curRef = null;
            }
        }
    }

    protected function setRightFromBuff() {

    }

    public function pushGrouping(string $token) : StatementBuilder {
        if ($token === '(') {
            $pred = new StatementBuilder($this);
            $pred->isGrouped = true;
            if ($this->leafA === null) {
                $this->leafA = $pred;
            } else if ($this->op === null) {
                throw new \Exception("'(' found after left-hand expression without operator");
            } else if ($this->leafB === null) {
                $this->leafB = $pred;
            } else {
                throw new \Exception("'(' found after right-hand expression");
            }

            return $pred;
        } else {
            $this->setRefOnPredicateBranch();
            $pred = $this->getParent();
            if ($pred === null) {
                throw new \Exception("')' found without matching '('");
            }

            return $pred;
        }
    }

    public function pushOperator(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->curRef->append($token);
        } else {
            $this->setLeftFromBuff();
            if ($this->leafA === null) {
                throw new \Exception("'$token' must be after variable/scalar reference");
            }

            if ($this->leafB !== null) {
                $pred = new StatementBuilder($this);
                $pred->setLeafA($this->leafB);
                $pred->pushOperator($token);
                $this->leafB = $pred;
                return $pred;
            } else {
                $this->op = $token;
                return $this;
            }
        }
    }

    protected function castLabel(string $label) : string {
        return $label;
    }

    public function pushQuote(string $token) : StatementBuilder {
        if ($this->inEscape) {
            $this->curRef->append($token);
            $this->inEscape = false;
        } else if ($token === '\\') {
            if (!$this->inQuote) {
                throw new \Exception("can't escape outside of strings");
            }

            $this->inEscape = true;
        } else if (!$this->inQuote) {
            if ($this->curRef !== null) {
                throw new \Exception("can't start quoted string after @{$this->curRef}@");
            }

            $this->inQuote = $token;
            $this->curRef  = (new QueryReference())->setToString();
        } else if ($this->inQuote === $token) {
            $this->inQuote = null;
            if ($this->leafA === null) {
                $this->leafA = $this->curRef;
            } else if ($this->op && $this->leafB === null) {
                $this->leafB = $this->curRef;
            } else {
                throw new \Exception("can't start quoted string here TODO: EXPRESSION FRAGMENT");
            }

            $this->curRef = null;
        }

        return $this;
    }

    public function pushSpace(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->curRef->append($token);
        } else {
            $this->setRefOnPredicateBranch();
        }

        return $this;
    }

    public function pushChars(string $chars) : StatementBuilder {
        if ($this->curRef === null) {
            $this->curRef = new QueryReference($chars);
        } else {
            $this->curRef->append($chars);
        }

        return $this;
    }

    public function cleanup() {
        $this->setRefOnPredicateBranch();
    }

    public function __toString() {
        $l = '';
        $r = '';
        if ($this->isGrouped) {
            $l = '(';
            $r = ')';
        }

        $buff = $l . (string) $this->leafA;
        if ($this->op) {
            $buff .= " {$this->op} " . (string) $this->leafB;
        }

        $buff .= $r;
        return $buff;
    }

    public function jsonSerialize() {
        return [
            'left' => $this->leafA->jsonSerialize(),
            'op' => $this->op,
            'right' => $this->leafB ? $this->leafB->jsonSerialize() : null,
            'buff' => $this->curRef
        ];
    }

    /**
     * Crawls tree and compares precedence of the current node's operator to the
     * right node's operator. If the current operator has a higher precedence,
     * do a left rotation. If rotated once, invoke rebalance again on this node.
     * Otherwise, invoke rebalance on right branch. Proof:
     *
     * - given: `(a op1 b)` represents an operator node with left leaf `a` and right leaf `b`
     * - if `(a op1 (b op2 c))` with `op1 >= op2`, evaluating left-to-right follows
     * expected evaluation order
     *   - rebalance `a` and `b op2 c`
     * - if `(a op1 (b op2 c))` with `op1 < op2`, evaluating left-to-right does
     * not follow expected evaluation order:
     *   - rotate left on `b`, such that tree becomes `((a op1 b) op2 c)`
     *   - repeat rebalance on self
     *
     * @return StatementBuilder
     */
    public function rebalance() : StatementBuilder {
        $op1 = $this->op;
        $op2 = null;
        if ($this->leafB instanceof StatementBuilder) {
            $op2 = $this->leafB->op;
        }

        if ($op2 && OperatorPrecedence::compareL2R($op1, $op2) === 1) {
            $this->rotateLeft();
            $this->rebalance();
        } else {
            if ($this->leafA instanceof StatementBuilder) {
                $this->leafA->rebalance();
            }

            if ($this->leafB instanceof StatementBuilder) {
                $this->leafB->rebalance();
            }
        }

        return $this;
    }

    protected function rotateLeft() {
        $rtOp = $this->leafB->op;
        $rtLeafB = $this->leafB->leafB;

        $leafA        = new StatementBuilder($this);
        $leafA->leafA = $this->leafA;
        $leafA->op    = $this->op;
        $leafA->leafB = $this->leafB->leafA;

        $this->op        = $rtOp;
        $this->leafA     = $leafA;
        $this->leafB     = $rtLeafB;
        $rtLeafB->parent = $this;
    }

    public function getRoot() : StatementBuilder {
        $ptr = $this;
        while ($ptr->getParent() !== null) {
            $ptr = $ptr->getParent();
        }

        return $ptr;
    }

    public function build() : Statement {
        $root = $this->buildPredicate();
        return new Statement($root);
    }

    public function buildPredicate() : PredicateInterface {
        $left = $this->leafA;
        if ($left instanceof StatementBuilder) {
            $left = $left->buildPredicate();
        }

        $right = $this->leafB;
        if ($right instanceof StatementBuilder) {
            $right = $right->buildPredicate();
        }

        return PredicateFactory::fromOperator($this->op ? $this->op : 'leafa', $left, $right);
    }
}
