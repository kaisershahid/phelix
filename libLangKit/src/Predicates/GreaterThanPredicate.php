<?php
namespace DinoTech\LangKit\Predicates;

use DinoTech\LangKit\ContextInterface;

class GreaterThanPredicate extends AbstractPredicate {
    protected function doEval(ContextInterface $context, $left, $right) {
        if ($this->op == '>=') {
            return $left >= $right;
        } else {
            return $left > $right;
        }
    }
}
