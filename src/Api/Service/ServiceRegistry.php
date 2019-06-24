<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceRegistryConfig;
use DinoTech\Phelix\Api\Service\Registry\Index;

class ServiceRegistry {
    private $services;

    private $pendingServices;

    public function __construct() {
        $this->services = new Index();
    }

    public function getByInterface(string $interface) {

    }

    public function getByQuery(string $query) {

    }

    public function registerService(ServiceConfig $serviceConfig) {

    }

    public function loadFromConfig(ServiceRegistryConfig $registryConfig) {
        foreach ($registryConfig->getServiceConfigs() as $config) {
            $this->registerService($config);
        }
    }

    public function unloadFromConfig(ServiceRegistryConfig $registryConfig) {
        foreach ($registryConfig->getServiceConfigs() as $config) {
            //$this->unregisterService($config);
        }
    }
}
