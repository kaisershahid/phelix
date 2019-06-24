<?php
namespace DinoTech\TestBundle;

use DinoTech\Phelix\Api\Bundle\BundleActivator;
use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Bundle\DefaultActivator;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;

class Activator extends DefaultActivator {
    public function activate(ServiceRegistry $serviceRegistry) {
        echo self::class . " >> activate()\n";
        $conf = $this->getServiceRegistryConfig();
        print_r($conf);
    }

    public function deactivate(ServiceRegistry $serviceRegistry) {
        // TODO: Implement deactivate() method.
    }

}
