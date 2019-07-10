<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Config\ServiceProperties;

/**
 * Provides high-level information on the service. Primarily used for service
 * activation.
 */
class ServiceContext {
    /** @var BundleManifest */
    private $manifest;
    /** @var ServiceProperties */
    private $properties;

    public function __construct(BundleManifest $manifest, ServiceProperties $properties) {
        $this->manifest = $manifest;
        $this->properties = $properties;
    }

    public function getManifest() : BundleManifest {
        return $this->manifest;
    }

    public function getProperties() : ServiceProperties {
        return $this->properties;
    }
}
