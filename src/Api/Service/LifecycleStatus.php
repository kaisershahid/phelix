<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\StdLib\Enum;

class LifecycleStatus extends Enum {
    const DISABLED = 'disabled';
    const STARTING = 'starting';
    const ERROR = 'error';
    const UNSATISFIED = 'unsatisfied';
    const SATISFIED = 'satisfied';
    const ACTIVE = 'active';
}
