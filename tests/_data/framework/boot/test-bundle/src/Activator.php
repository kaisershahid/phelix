<?php
namespace DinoTech\TestBundle;

use DinoTech\Phelix\Api\Bundle\BundleActivator;
use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;

class Activator implements BundleActivator {
    public function setFramework(Framework $framework): BundleActivator {
        return $this;
    }

    public function setManifest(BundleManifest $manifest): BundleActivator {
        return $this;
    }

    public function activate(ServiceRegistry $serviceRegistry) {
        echo "(((((( ACTIVATING!\n";
    }

    public function deactivate(ServiceRegistry $serviceRegistry) {
        // TODO: Implement deactivate() method.
    }

}
