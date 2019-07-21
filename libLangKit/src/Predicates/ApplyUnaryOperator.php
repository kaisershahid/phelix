<?php
namespace DinoTech\LangKit\Predicates;

use DinoTech\LangKit\ContextInterface;

/**
 * @todo support increment/decrement (need Reference to be passed in)
 */
class ApplyUnaryOperator {
    const ALLOWED_PREFIX_OPS = ['!' => true, '-' => true, '+' => true];
    const ALLOWED_POSTFIX_OPS = [];

    /** @var string */
    private $pre;
    /** @var string */
    private $post;
    private $value;
    /** @var ContextInterface */
    private $context;

    public function __construct($value, ContextInterface $context) {
        $this->value = $value;
        $this->context = $context;
    }

    /**
     * @param string $pre
     * @return ApplyUnaryOperator
     */
    public function setPre(string $pre = null): ApplyUnaryOperator {
        $this->pre = $pre;
        return $this;
    }

    /**
     * @param string $post
     * @return ApplyUnaryOperator
     */
    public function setPost(string $post = null): ApplyUnaryOperator {
        $this->post = $post;
        return $this;
    }

    public function evaluate() {
        if ($this->pre !== null) {
            return $this->evaluatePrefix();
        }

        // @todo evaluate postfix
    }

    public function evaluatePrefix() {
        if (!isset(self::ALLOWED_PREFIX_OPS[$this->pre])) {
            throw new \Exception("prefix operator not allowed: {$this->pre}");
        }

        $val = $this->value;
        if ($this->pre === '-') {
            return 0 - $val;
        }
    }
}
