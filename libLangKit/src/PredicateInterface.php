<?php
namespace DinoTech\LangKit;

/**
 * Wrapper for predicate. Simply takes a context and returns a value from
 * evaluating the underlying predicate.
 */
interface PredicateInterface {
    /**
     * @param ContextInterface $context
     * @return mixed
     */
    public function executePredicate(ContextInterface $context);
}
