<?php
namespace DinoTech\LangKit\Predicates;

use DinoTech\LangKit\ContextInterface;
use DinoTech\LangKit\PredicateInterface;
use DinoTech\LangKit\ReferenceInterface;
use DinoTech\StdLib\Collections\ArrayUtils;

/**
 * Represents a unary operation.
 */
class UnaryPredicate implements PredicateInterface {
    protected $value;
    protected $prefixOp;
    protected $postfixOp;

    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @param string $prefixOp
     * @return UnaryPredicate
     */
    public function setPrefixOp(string $prefixOp = null) : UnaryPredicate {
        $this->prefixOp = $prefixOp;
        return $this;
    }

    /**
     * @param string $postfixOp
     * @return UnaryPredicate
     */
    public function setPostfixOp(string $postfixOp = null) : UnaryPredicate {
        $this->postfixOp = $postfixOp;
        return $this;
    }

    public function executePredicate(ContextInterface $context) {
        $value = $this->value;
        if ($value instanceof PredicateInterface) {
            $value = $value->executePredicate($context);
        }

        $unary = new StandardUnaryOperator();
        if ($this->prefixOp) {
            return $unary->evaluatePrefix($this->prefixOp, $value, $context);
        } else if ($this->postfixOp) {
            return $unary->evaluatePostfix($this->prefixOp, $value, $context);
        } else {
            if ($value instanceof ReferenceInterface) {
                return $value->evaluate($context);
            }

            return $value;
        }
    }
}
