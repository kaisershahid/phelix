<?php
namespace DinoTech\Phelix\Expressions\Predicates;

use DinoTech\Phelix\Expressions\ContextInterface;
use DinoTech\Phelix\Expressions\PredicateInterface;
use DinoTech\Phelix\Expressions\ReferenceInterface;
use DinoTech\StdLib\Collections\ArrayUtils;

/**
 * Use this for wrapping a single value or grouped set of predicates. Allows setting
 * pre- and postfix operators.
 */
class SingleValuePredicate implements PredicateInterface {
    protected $value;
    protected $prefixOp;
    protected $postfixOp;

    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @param string $prefixOp
     * @return SingleValuePredicate
     */
    public function setPrefixOp(string $prefixOp = null) : SingleValuePredicate {
        $this->prefixOp = $prefixOp;
        return $this;
    }

    /**
     * @param string $postfixOp
     * @return SingleValuePredicate
     */
    public function setPostfixOp(string $postfixOp = null) : SingleValuePredicate {
        $this->postfixOp = $postfixOp;
        return $this;
    }

    public function executePredicate(ContextInterface $context) {
        $value = $this->value;
        if ($value instanceof ReferenceInterface) {
            $value = $value->isDynamic() ?
                $context->lookupVar($value->getRawValue()) :
                $value->getLiteralValue();
        } else if ($value instanceof PredicateInterface) {
            $value = $value->executePredicate($context);
        }

        return (new ApplyUnaryOperator($value, $context))
            ->setPost($this->postfixOp)
            ->setPre($this->prefixOp)
            ->evaluate();
    }
}
