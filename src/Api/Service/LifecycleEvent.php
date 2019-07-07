<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\StdLib\Enum;

class LifecycleEvent extends Enum {
    const ACTIVATE = 'activate';
    const DEACTIVATE = 'deactivate';

    public function getTopic() : string {
        return self::class . '\\' . $this->name();
    }
}
