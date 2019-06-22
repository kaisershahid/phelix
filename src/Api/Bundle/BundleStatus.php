<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\StdLib\Enum;

class BundleStatus extends Enum {
    const CONFLICT = 'conflict';
    const REGISTERED = 'registered';
    const ACTIVE = 'active';
}
