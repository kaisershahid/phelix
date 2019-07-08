<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\StdLib\Enum;
use DinoTech\StdLib\Strings\StringUtils as Str;

/**
 * @method static ONE()
 * @method static ONE_OPTIONAL()
 * @method static MANY()
 * @method static MANY_OPTIONAL()
 */
class ReferenceCardinality extends Enum {
    /**
     * Must have 1
     */
    const ONE = true;
    /**
     * May have 0 or 1
     */
    const ONE_OPTIONAL = false;
    /**
     * Must have at least 1
     */
    const MANY = true;
    /**
     * May have 0 or more
     */
    const MANY_OPTIONAL = false;

    public function isMandatory() : bool {
        return !Str::contains('OPTIONAL', $this->name());
    }

    public function isOne() {
        return Str::contains('ONE', $this->name());
    }

    public function isMany() {
        return !$this->isOne();
    }
}
