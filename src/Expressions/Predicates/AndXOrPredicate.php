<?php
namespace DinoTech\Phelix\Expressions\Predicates;

use DinoTech\Phelix\Expressions\ContextInterface;

class AndXOrPredicate extends AbstractPredicate {
    protected function doEval(ContextInterface $context, $left, $right) {
        if ($this->op == '&&') {
            return $left && $right;
        } else if ($this->op == '||') {
            return $left || $right;
        } else if ($this->op == 'xor') {
            return $left xor $right;
        }
    }
}
