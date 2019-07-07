<?php
namespace DinoTech\TestBundle;

use DinoTech\Phelix\Api\Bundle\BundleActivatorInterface;
use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Bundle\DefaultActivatorInterface;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;

class Activator extends DefaultActivatorInterface {
    public function activate(ServiceRegistry $serviceRegistry) {
        echo self::class . " >> activate()\n";
        $conf = $this->getServiceRegistryConfig();
        print_r($conf);
    }

    public function deactivate(ServiceRegistry $serviceRegistry) {
        // TODO: Implement deactivate() method.
    }

}
