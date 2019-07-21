<?php
namespace DinoTech\LangKit;

/**
 * Wrapper for basic predicate.
 */
interface PredicateInterface {
    /**
     * @param ContextInterface $context
     * @return mixed|null
     */
    public function executePredicate(ContextInterface $context);
}
