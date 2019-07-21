<?php
namespace DinoTech\LangKit\Predicates;

use DinoTech\LangKit\ContextInterface;

class EqualsPredicate extends AbstractPredicate {
    protected function doEval(ContextInterface $context, $left, $right) {
        if ($this->op === '==') {
            return $left == $right;
        } else if ($this->op == '!=') {
            return $left != $right;
        } else if ($this->op == '!==') {
            return $left !== $right;
        } else {
            return $left === $right;
        }
    }
}
