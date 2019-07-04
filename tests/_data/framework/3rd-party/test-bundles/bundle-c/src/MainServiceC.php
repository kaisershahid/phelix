<?php
namespace DinoTech\BundleC;

class MainServiceC {
    protected function activate() {
        codecept_debug("activate: " . self::class);
    }

    protected function deactivate() {

    }
}
