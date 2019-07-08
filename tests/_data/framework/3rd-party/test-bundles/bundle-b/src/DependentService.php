<?php
namespace DinoTech\BundleB;

use DinoTech\BundleA\MainService;

class DependentService {
    private $serviceC;

    private function doActivate() {
        codecept_debug("activate: " . self::class);
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
}
