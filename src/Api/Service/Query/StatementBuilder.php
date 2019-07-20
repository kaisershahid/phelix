<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\OperatorPrecedence;
use DinoTech\Phelix\Expressions\PredicateFactory;
use DinoTech\Phelix\Expressions\PredicateInterface;
use DinoTech\Phelix\Expressions\Predicates\BranchedPredicate;

/**
 */
class StatementBuilder implements \JsonSerializable {
    /** @var bool */
    private $inEscape = false;
    /** @var string */
    private $inQuote;
    /** @var bool */
    protected $isGrouped = false;
    /** @var QueryReference */
    private $curRef;
    /** @var QueryReference */
    private $lastRef;
    /** @var NTree */
    private $tree;
    /** @var NTree */
    private $treePtr;

    public function __construct() {
        $this->tree = (new NTree())->setComparator(new OperatorPrecedence());
        $this->treePtr = $this->tree;
    }

    protected function dereference() : ?QueryReference {
        $ref = $this->curRef;
        $this->lastRef = $ref;
        $this->curRef = null;
        return $ref;
    }

    /**
     * Places the current reference on either the left or right branch. If left
     * branch is already set and no operator was seen, throws an exception.
     * @throws \Exception
     */
    protected function pushReference() {
        $ref = $this->dereference();
        if ($ref === null) {
            return;
        }

        try {
            $this->treePtr = $this->treePtr->pushValue($ref);
        } catch (NTreeException $e) {
            throw new StatementBuilderException("pushReference($ref) failed: {$e->getMessage()}");
        }
    }

    protected $groupStack = [];

    public function pushGrouping(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->curRef->append($token);
        } else if ($token === '(') {
            if ($this->treePtr->wasLastValue()) {
                throw new StatementBuilderException("'(' must be start of statement or after an operator");
            }

            $this->groupStack[] = $this->treePtr;
            $this->treePtr = $this->treePtr->pushGroupedSubtree();
        } else {
            // @todo throw exception if ref null?
            $ref = $this->dereference();
            $this->treePtr->pushValue($ref)->getParent();
            $this->treePtr = array_pop($this->groupStack);
            if ($this->treePtr === null) {
                throw new \Exception("')' found without matching '('");
            }
        }

        return $this;
    }

    public function pushOperator(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->curRef->append($token);
        } else {
            $ref = $this->dereference();
            if ($ref !== null) {
                $this->treePtr = $this->treePtr->pushValue($ref);
            }

            $this->treePtr = $this->treePtr->pushOperator($token);
        }

        return $this;
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
                throw new StatementBuilderException("can't start quoted string after: {$this->curRef}");
            }

            $this->inQuote = $token;
            $this->curRef  = (new QueryReference())->setToString();
        } else if ($this->inQuote === $token) {
            $this->inQuote = null;
            if ($this->treePtr->wasLastValue()) {
                throw new StatementBuilderException("can't start a string after a reference/value: {$this->lastRef}");
            }

            $this->treePtr = $this->treePtr->pushValue($this->dereference());
        }

        return $this;
    }

    public function pushSpace(string $token) : StatementBuilder {
        if ($this->inQuote) {
            $this->curRef->append($token);
        } else {
            $this->pushReference();
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
        $this->pushReference();
    }

    public function __toString() {
        return 'todo';
    }

    public function jsonSerialize() {
        return [
            'todo'
        ];
    }

    public function getRoot() : NTree {
        $ptr = $this->tree;
        $parent = $ptr->getParent();
        while ($parent !== null) {
            $ptr = $parent;
            $parent = $ptr->getParent();
        }

        return $this->tree;
    }

    public function build() : Statement {
        $root = $this->buildPredicate();
        return new Statement($root);
    }

    public function buildPredicate() : PredicateInterface {
        return null;
    }
}
