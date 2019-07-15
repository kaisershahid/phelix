<?php
namespace DinoTech\Phelix\Expressions\Predicates;

use DinoTech\Phelix\Expressions\ContextInterface;

/**
 * Simply returns Leaf A -- this happens in one of the following:
 *
 * - `query_is_reference`
 * - `(nested expression)`
 */
class LeafAPredicate extends AbstractPredicate {
    public function doEval(ContextInterface $context, $left, $right) {
        return $left;
    }
}
