<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Framework;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\StdLib\Filesys\Path;

class DefaultActivator implements BundleActivator {
    /** @var Framework */
    private $framework;
    /** @var BundleManifest */
    private $manifest;
    /** @var array */
    private $serviceConfig;

    public function setFramework(Framework $framework): BundleActivator {
        $this->framework = $framework;
        return $this;
    }

    public function setManifest(BundleManifest $manifest): BundleActivator {
        $this->manifest = $manifest;
        return $this;
    }

    public function activate(ServiceRegistry $serviceRegistry) {
        $this->serviceConfig = $this->getServiceConfig();
        $serviceRegistry->loadFromConfig($this->serviceConfig);
    }

    public function getServiceConfig() : array {
        $path = Path::join($this->manifest->getBundleRoot(), BundleReader::FILE_SERVICE_REGISTRY);
        if (file_exists($path)) {
            return (new GenericConfig())->noCallbacks()->loadYamlFromFile($path);
        }

        return [];
    }

    public function deactivate(ServiceRegistry $serviceRegistry) {
        $serviceRegistry->unloadFromConfig($this->serviceConfig);
    }
}
