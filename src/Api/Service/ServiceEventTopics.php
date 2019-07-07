<?php
namespace DinoTech\Phelix\Api\Service;

class ServiceEventTopics {
    /** @var string Triggered after service is activated. */
    const ACTIVATED = self::class . '\\Activated';
    /** @var string Triggered if there's an activation error. */
    const ERROR = self::class . '\\ERROR';
    /** @var string Triggered before service is deactivated. */
    const DEACTIVATING = self::class . '\\Deactivating';
}
