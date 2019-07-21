<?php
namespace DinoTech\LangKit;

/**
 * Supports a generic way to apply unary operators to a variable or scalar value.
 */
interface UnaryOperatorInterface {
    /**
     * @param string $operator
     * @param ReferenceInterface|mixed $operand
     * @param ContextInterface $context
     * @return mixed
     * @throws UnaryOperatorException
     */
    public function evaluatePrefix(string $operator, $operand, ContextInterface $context);

    /**
     * @param string $operator
     * @param ReferenceInterface|mixed $operand
     * @param ContextInterface $context
     * @return mixed
     * @throws UnaryOperatorException
     */
    public function evaluatePostfix(string $operator, $operand, ContextInterface $context);
}
