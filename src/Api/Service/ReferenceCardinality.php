<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\StdLib\Enum;

class ReferenceCardinality extends Enum {
    const ONE = true;
    const ONE_OPTIONAL = false;
    const AT_LEAST_ONE = true;
    const MULTIPLE = false;

    public function isMandatory() : bool {
        return $this->value();
    }
}
