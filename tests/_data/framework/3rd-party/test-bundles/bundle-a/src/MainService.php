<?php
namespace DinoTech\BundleA;

class MainService {
    protected function activate() {
        codecept_debug("activate: " . self::class);
    }

    protected function deactivate() {

    }
}
