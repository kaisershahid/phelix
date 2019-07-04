<?php
namespace DinoTech\BundleB;

class DependentService {
    private function doActivate() {
        codecept_debug("activate: " . self::class);
    }
}
