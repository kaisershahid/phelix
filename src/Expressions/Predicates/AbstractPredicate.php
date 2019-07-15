<?php
namespace DinoTech\Phelix\Expressions\Predicates;

use DinoTech\Phelix\Expressions\ContextInterface;
use DinoTech\Phelix\Expressions\PredicateInterface;
use DinoTech\Phelix\Expressions\ReferenceInterface;
use DinoTech\Phelix\Expressions\BasicContext;

abstract class AbstractPredicate implements PredicateInterface, \JsonSerializable {
    protected $op;
    protected $left;
    protected $right;

    public function __construct(string $op, $left, $right = null) {
        $this->op = $op;
        $this->left = $left;
        $this->right = $right;
    }

    public function jsonSerialize() {
        return [
            'op' => $this->op,
            'leafA' => $this->left instanceof \JsonSerializable ?
                $this->left->jsonSerialize() : null,
            'leafB' => $this->right instanceof \JsonSerializable ?
                $this->right->jsonSerialize() : null,
        ];
    }

    /**
     * @param ContextInterface $context
     * @return mixed
     */
    public function executePredicate(ContextInterface $context) {
        $left = $this->extractNodeValue($this->left, $context);
        $right = $this->extractNodeValue($this->right, $context);

        return $this->doEval($context, $left, $right);
    }

    /**
     * @param ReferenceInterface|PredicateInterface|mixed $node
     * @param ContextInterface $context
     * @return mixed|null
     */
    public function extractNodeValue($node, ContextInterface $context) {
        if ($node instanceof ReferenceInterface) {
            return $node->isDynamic() ?
                $context->lookupVar($node->getRawValue()) :
                $node->getLiteralValue();
        } else if ($node instanceof  PredicateInterface) {
            return $node->executePredicate($context);
        }

        return $node;
    }

    abstract protected function doEval(ContextInterface $context, $left, $right);
}
