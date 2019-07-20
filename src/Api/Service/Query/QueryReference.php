<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\ReferenceInterface;

/**
 * Decorates a value/variable in query building.
 */
class QueryReference implements \JsonSerializable, ReferenceInterface {
    const REGEX_NUMBER = "#^-?(\d+|\d+\.\d+|(\d+|\d+\.\d+)e-?\d+)$#";
    const REGEX_INT = '#^-?\d+$#';
    const REGEX_BOOL = '#^true|false$#';
    const REGEX_NULL = '#^null$#';

    private $buff;
    private $isStr = false;
    private $isDyn;

    public function __construct(string $buff = '') {
        $this->buff = $buff;
    }

    public function setToString() : QueryReference {
        $this->isStr = true;
        return $this;
    }

    public function append(string $buff) : QueryReference {
        $this->buff .= $buff;
        return $this;
    }

    public function __toString() {
        if ($this->isDynamic()) {
            return $this->getRawValue();
        } else {
            return json_encode($this->getLiteralValue());
        }
    }

    public function jsonSerialize() {
        if ($this->isStr) {
            return (string) $this;
        } else {
            return $this->getLiteralValue();
        }
    }

    public function getType(): int {
        return 0;
    }

    public function isString() : bool {
        return $this->isStr;
    }

    public function isDynamic(): bool {
        if ($this->isDyn === null) {
            $this->isDyn =
                !$this->isStr &&
                !preg_match(self::REGEX_NUMBER, $this->buff) &&
                !preg_match(self::REGEX_BOOL, $this->buff) &&
                !preg_match(self::REGEX_NULL, $this->buff)
            ;
        }

        return $this->isDyn;
    }

    public function getLiteralValue() {
        if (preg_match(self::REGEX_NUMBER, $this->buff)) {
            if (!preg_match(self::REGEX_INT, $this->buff)) {
                return floatval($this->buff);
            } else {
                return intval($this->buff);
            }
        } else if (preg_match(self::REGEX_BOOL, $this->buff)) {
            return $this->buff === 'true';
        } else if (preg_match(self::REGEX_NULL, $this->buff)) {
            return null;
        }

        return $this->buff;
    }

    public function getRawValue(): string {
        return $this->buff;
    }
}
