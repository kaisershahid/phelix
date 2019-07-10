<?php
namespace DinoTech\BundleB;

use DinoTech\BundleA\MainService;
use DinoTech\Phelix\Api\Config\ServiceProperties;
use DinoTech\Phelix\Api\Service\ServiceContext;

class DependentService {
    private $serviceC;
    /** @var array */
    private $properties;

    private function doActivate(ServiceContext $context) {
        codecept_debug("activate: " . self::class);
        $this->properties = $context->getProperties()->jsonSerialize();
    }

    protected function bindMain(MainService $svc) {
        codecept_debug("bindMain! " . get_class($svc));
    }

    protected function unbindMain() {
        codecept_debug("unbindMain!");
    }

    public function getServiceC() {
        return $this->serviceC;
    }

    public function getProperties() : array {
        return $this->properties;
    }
}
