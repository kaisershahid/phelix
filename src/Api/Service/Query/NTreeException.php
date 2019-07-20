<?php
namespace DinoTech\Phelix\Api\Service\Query;

class NTreeException extends \Exception {
    const CODE_OPERATOR = 1;
    const CODE_VALUE = 2;

    public static function getOperatorException(string $message) {
        return new static($message, self::CODE_OPERATOR);
    }

    public static function getValueException(string $message) {
        return new static($message, self::CODE_VALUE);
    }
}
