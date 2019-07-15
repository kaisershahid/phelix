<?php
namespace DinoTech\Phelix\Expressions\Predicates;

use DinoTech\Phelix\Expressions\ContextInterface;

class GreaterThanPredicate extends AbstractPredicate {
    protected function doEval(ContextInterface $context, $left, $right) {
        if ($this->op == '>=') {
            return $left >= $right;
        } else {
            return $left > $right;
        }
    }
}
