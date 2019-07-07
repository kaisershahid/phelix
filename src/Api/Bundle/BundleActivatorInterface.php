<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Framework;
use DinoTech\Phelix\Api\Service\ServiceRegistry;

interface BundleActivatorInterface {
    public function setFramework(Framework $framework) : BundleActivatorInterface;

    public function setManifest(BundleManifest $manifest) : BundleActivatorInterface;

    public function activate(ServiceRegistry $serviceRegistry);

    public function deactivate(ServiceRegistry $serviceRegistry);
}
