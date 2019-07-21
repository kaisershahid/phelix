<?php
namespace DinoTech\LangKit\Predicates;

use DinoTech\LangKit\ContextInterface;
use DinoTech\LangKit\ReferenceInterface;
use DinoTech\LangKit\UnaryOperatorException;
use DinoTech\LangKit\UnaryOperatorInterface;

class StandardUnaryOperator implements UnaryOperatorInterface {
    const ALLOWED_PREFIX_OPS = ['!' => true, '-' => true, '+' => true, '--' => 'true', '++' => false];
    const ALLOWED_POSTFIX_OPS = ['--' => 'true', '++' => false];

    public function evaluatePrefix(string $operator, $operand, ContextInterface $context) {
        if (!isset(self::ALLOWED_PREFIX_OPS[$operator])) {
            throw new UnaryOperatorException("prefix operator not allowed: $operator");
        }

        $val = $this->extractValue($operand, $context);
        if ($operator === '-') {
            return 0 - $val;
        } else if ($operator === '!') {
            return !((bool) $val);
        } else if ($operator != '+') {
            $newVal = $val;
            if ($operator === '--') {
                $newVal -= 1;
            } else {
                $newVal += 1;
            }

            $this->updateContextValue($operator, $operand, $context, $newVal);
            return $val;
        }

        return $val;
    }

    public function evaluatePostfix(string $operator, $operand, ContextInterface $context) {
        if (!isset(self::ALLOWED_PREFIX_OPS[$operator])) {
            throw new UnaryOperatorException("prefix operator not allowed: $operator");
        }

        $val = $this->extractValue($operand, $context);
        $newVal = $val;
        if ($operator === '--') {
            $newVal -= 1;
        } else {
            $newVal += 1;
        }

        $this->updateContextValue($operator, $operand, $context, $newVal);
        return $newVal;
    }

    public function extractValue($operand, ContextInterface $context) {
        if ($operand instanceof ReferenceInterface) {
            return $operand->evaluate($context);
        }

        return $operand;
    }

    public function updateContextValue($operator, $operand, ContextInterface $context, $newVal) {
        if (!$operand instanceof ReferenceInterface) {
            throw new UnaryOperatorException("cannot apply $operator to a non-reference");
        }

        $context->setVar($operand->getRawValue(), $newVal);
    }
}
