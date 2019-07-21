<?php
namespace DinoTech\LangKit\Predicates;

use DinoTech\LangKit\ContextInterface;

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
