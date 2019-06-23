<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Framework;
use DinoTech\Phelix\Api\Service\ServiceRegistry;

interface BundleActivator {
    public function setFramework(Framework $framework) : BundleActivator;

    public function setManifest(BundleManifest $manifest) : BundleActivator;

    public function activate(ServiceRegistry $serviceRegistry);

    public function deactivate(ServiceRegistry $serviceRegistry);
}
