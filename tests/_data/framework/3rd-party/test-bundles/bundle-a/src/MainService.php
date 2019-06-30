<?php
namespace DinoTech\BundleA;

class MainService {
    protected function activate() {
        codecept_debug("ACT!!!!");
        return 'xyz';
    }

    protected function deactivate() {

    }
}
