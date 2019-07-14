<?php
namespace DinoTech\Phelix\Api\Service\Query;

/**
 * Decorates a value/variable in query building.
 */
class StatementReference implements \JsonSerializable {
    const REGEX_NUMBER = "#^-?(\d+|\d+\.\d+|(\d+|\d+\.\d+)e-?\d+)$#";
    const REGEX_INT = '#^-?\d+$#';

    private $buff;
    private $isStr = false;

    public function __construct(string $buff = '') {
        $this->buff = $buff;
    }

    public function setToString() : StatementReference {
        $this->isStr = true;
        return $this;
    }

    public function isString() : bool {
        return $this->isStr;
    }

    public function append(string $buff) : StatementReference {
        $this->buff .= $buff;
        return $this;
    }

    public function __toString() {
        $l = '';
        $r = '';
        $v = $this->buff;
        if ($this->isStr) {
            $l = '"';
            $r = '"';
            $v = addslashes($v);
        }

        return $l . $v . $r;
    }

    public function jsonSerialize() {
        if ($this->isStr) {
            return (string) $this;
        } else {
            return $this->getValue();
        }
    }

    public function getValue() {
        if (preg_match(self::REGEX_NUMBER, $this->buff)) {
            if (!preg_match(self::REGEX_INT, $this->buff)) {
                return floatval($this->buff);
            } else {
                return intval($this->buff);
            }
        }

        return $this->buff;
    }
}
