<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\StdLib\Enum;

/**
 * @method BundleStatus REGISTERED
 * @method BundleStatus ERROR
 * @method BundleStatus ACTIVE
 */
class BundleStatus extends Enum {
    const REGISTERED = 'registered';
    const ERROR = 'error';
    const ACTIVE = 'active';
}
