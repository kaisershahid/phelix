<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\LangKit\Utils\StringBuilder;
use DinoTech\Phelix\Expressions\OperatorPrecedence;
use DinoTech\LangKit\PredicateInterface;

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
    /** @var StringBuilder */
    private $str;

    public function __construct() {
        $this->tree    = (new NTree())->setComparator(new OperatorPrecedence());
        $this->treePtr = $this->tree;
        $this->str     = new StringBuilder();
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
        if ($this->str->isStarted()) {
            $this->str->push($token);
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
        if ($this->str->isStarted()) {
            $this->str->push($token);
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
        if ($token == '\\' && $this->str->isComplete()) {
            throw new StatementBuilderException("can't escape outside of strings");
        } else if ($this->curRef !== null) {
            throw new StatementBuilderException("can't start string after: {$this->curRef}");
        } else if (!$this->str->isStarted()) {
            $this->str->start($token);
        } else {
            $this->str->push($token);
            if ($this->str->isComplete()) {
                $this->curRef = (new QueryReference())->setToString()
                    ->append($this->str->getString());
                $this->str->reset();
                $this->treePtr = $this->treePtr->pushValue($this->dereference());
            }
        }

        return $this;
    }

    public function pushSpace(string $token) : StatementBuilder {
        if ($this->str->isStarted()) {
            $this->str->push($token);
        } else {
            $this->pushReference();
        }

        return $this;
    }

    public function pushChars(string $chars) : StatementBuilder {
        if ($this->str->isStarted()) {
            $this->str->push($chars);
        } else if ($this->curRef === null) {
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

        return $ptr;
    }

    public function build() : Statement {
        $root = $this->buildPredicate();
        return new Statement($root);
    }

    public function buildPredicate() : PredicateInterface {
        return null;
    }
}
