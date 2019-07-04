<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\StdLib\Enum;

/**
 * @method LifecycleStatus DISABLED
 * @method LifecycleStatus STARTING
 * @method LifecycleStatus UNSATISFIED
 * @method LifecycleStatus SATISFIED
 * @method LifecycleStatus ERROR
 * @method LifecycleStatus ACTIVE
 */
class LifecycleStatus extends Enum {
    const DISABLED = 'disabled';
    const ERROR = 'error';
    const STARTING = 'starting';
    const UNSATISFIED = 'unsatisfied';
    const SATISFIED = 'satisfied';
    const ACTIVE = 'active';
}
