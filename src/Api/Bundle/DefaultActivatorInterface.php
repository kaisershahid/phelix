<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceRegistryConfig;
use DinoTech\Phelix\Framework;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\StdLib\Filesys\Path;

class DefaultActivatorInterface implements BundleActivatorInterface {
    /** @var Framework */
    private $framework;
    /** @var BundleManifest */
    private $manifest;
    /** @var array */
    private $serviceConfig;

    public function setFramework(Framework $framework): BundleActivatorInterface {
        $this->framework = $framework;
        return $this;
    }

    public function setManifest(BundleManifest $manifest): BundleActivatorInterface {
        $this->manifest = $manifest;
        return $this;
    }

    public function activate(ServiceRegistry $serviceRegistry) {
        $serviceRegistry->loadBundle($this->manifest);
        $this->serviceConfig = $this->getServiceRegistryConfig();
        print_r($this->serviceConfig);
        //$serviceRegistry->loadFromConfig($this->serviceConfig);
    }

    public function getServiceRegistryConfig() : ServiceRegistryConfig {
        $path = Path::join($this->manifest->getBundleRoot(), BundleReader::FILE_SERVICE_REGISTRY);
        $conf = [];
        if (file_exists($path)) {
            $conf = (new GenericConfig())->noCallbacks()->loadYamlFromFile($path);
        }

        return new ServiceRegistryConfig($conf);
    }

    public function deactivate(ServiceRegistry $serviceRegistry) {
        $serviceRegistry->unloadFromConfig($this->serviceConfig);
    }
}
