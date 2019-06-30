<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\StdLib\Enum;

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
        return $this->value();
    }
}
