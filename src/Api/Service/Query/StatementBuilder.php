<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\OperatorPrecedence;

/**
 * Builds a binary tree representing the expression.
 */
class StatementBuilder implements \JsonSerializable {
    /** @var StatementBuilder */
    private $parent;
    /** @var bool */
    private $inEscape = false;
    /** @var string */
    private $inQuote;

    /** @var StatementReference|StatementBuilder */
    private $left;
    private $op;
    /** @var StatementReference|StatementBuilder */
    private $right;
    /** @var StatementReference */
    private $buff;

    public function __construct(StatementBuilder $parent = null) {
        $this->parent = $parent;
    }

    public function getParent() : StatementBuilder {
        return $this->parent;
    }

    protected function resolveBuff() {
        if ($this->buff !== null) {
            if ($this->left === null) {
                $this->left = $this->buff;
            } else if ($this->right === null) {
                $this->right = $this->buff;
            } else {
                throw new \Exception("dangling @{$this->buff}@");
            }

            $this->buff = null;
        }
    }

    protected function setLeft($label) : StatementBuilder {
        $this->left = $label;
        return $this;
    }

    protected function setLeftFromBuff() {
        if ($this->left === null) {
            if ($this->buff !== null) {
                $this->left = $this->buff;
                $this->buff = null;
            }
        }
    }

    protected function setRightFromBuff() {

    }

    public function pushGrouping(string $token) : StatementBuilder {
        if ($token === '(') {
            $pred = new StatementBuilder($this);
            if ($this->left === null) {
                $this->left = $pred;
            } else if ($this->op === null) {
                throw new \Exception("'(' found after left-hand expression without operator");
            } else if ($this->right === null) {
                $this->right = $pred;
            } else {
                throw new \Exception("'(' found after right-hand expression");
            }

            return $pred;
        } else {
            $pred = $this->getParent();
            if ($pred === null) {
                throw new \Exception("')' found without matching '('");
            }

            return $pred;
        }
    }

    public function pushOperator(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->buff->append($token);
        } else {
            $this->setLeftFromBuff();
            if ($this->left === null) {
                throw new \Exception("'$token' must be after variable/scalar reference");
            }

            if ($this->right !== null) {
                $pred = new StatementBuilder($this);
                $pred->setLeft($this->right);
                $pred->pushOperator($token);
                $this->right = $pred;
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
            $this->buff->append($token);
            $this->inEscape = false;
        } else if ($token === '\\') {
            if (!$this->inQuote) {
                throw new \Exception("can't escape outside of strings");
            }

            $this->inEscape = true;
        } else if (!$this->inQuote) {
            if ($this->buff !== null) {
                throw new \Exception("can't start quoted string after @{$this->buff}@");
            }

            $this->inQuote = $token;
            $this->buff = (new StatementReference())->setToString();
        } else if ($this->inQuote === $token) {
            $this->inQuote = null;
            if ($this->left === null) {
                $this->left = $this->buff;
            } else if ($this->op && $this->right === null) {
                $this->right = $this->buff;
            } else {
                throw new \Exception("can't start quoted string here TODO: EXPRESSION FRAGMENT");
            }

            $this->buff = null;
        }

        return $this;
    }

    public function pushSpace(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->buff->append($token);
        } else {
            $this->resolveBuff();
        }

        return $this;
    }

    public function pushChars(string $chars) : StatementBuilder {
        if ($this->buff === null) {
            $this->buff = new StatementReference($chars);
        } else {
            $this->buff->append($chars);
        }

        return $this;
    }

    public function cleanup() {
        $this->resolveBuff();
    }

    public function __toString() {
        if ($this->left instanceof StatementBuilder) {
            return '(' . (string)$this->left . ')';
        }

        $buff = $this->left;
        if ($this->op) {
            $buff .= " {$this->op} " . (string) $this->right;
        }

        return $buff;
    }

    public function jsonSerialize() {
        return [
            'left' => $this->left->jsonSerialize(),
            'op' => $this->op,
            'right' => $this->right->jsonSerialize(),
            'buff' => $this->buff
        ];
    }

    public function rebalance() : StatementBuilder {
        $op1 = $this->op;
        $op2 = null;
        if ($this->right instanceof StatementBuilder) {
            $op2 = $this->right->op;
        }

        if (OperatorPrecedence::compareL2R($op1, $op2) === 1 && $op2) {
            $this->rotateLeft();
        }

        return $this;
    }

    public function getRoot() : StatementBuilder {
        $ptr = $this;
        while ($ptr->parent !== null) {
            $ptr = $ptr->parent;
        }

        return $ptr;
    }

    protected function rotateLeft() {
        $op = $this->right->op;
        $right = $this->right->right;
        $left = new StatementBuilder();
        $left->left = $this->left;
        $left->op = $this->op;
        $left->right = $this->right->left;
        $this->op = $op;
        $this->left = $left;
        $this->right = $right;
    }
}
