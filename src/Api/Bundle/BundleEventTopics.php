<?php
namespace DinoTech\Phelix\Api\Bundle;

class BundleEventTopics {
    const REGISTERED   = self::class . '\\Registered';
    const ACTIVATED    = self::class . '\\Activated';
    const DEACTIVATING = self::class . '\\Deactivating';
    const ERROR        = self::class . '\\Error';
}
