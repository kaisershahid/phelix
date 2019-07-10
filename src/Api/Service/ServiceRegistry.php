<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Bundle\BundleReader;
use DinoTech\Phelix\Api\Config\ConfigBinderInterface;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Config\ServiceRegistryConfig;
use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\Phelix\Api\Service\Lifecycle\DefaultManager;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;

class ServiceRegistry implements ServiceRegistryInterface {
    /** @var Index */
    private $services;
    /** @var DefaultManager */
    private $manager;
    /** @var EventManagerInterface */
    private $eventManager;
    /** @var ConfigBinderInterface */
    private $configBinder;

    private $pendingServices;

    public function __construct(Index $services = null, DefaultManager $manager = null) {
        $this->services = $services ?: new Index();
        $this->manager = $manager ?: new DefaultManager($this->services);
    }

    /**
     * @param EventManagerInterface $eventManager
     * @return ServiceRegistry
     */
    public function setEventManager(EventManagerInterface $eventManager): ServiceRegistry {
        $this->eventManager = $eventManager;
        $this->manager->setEventManager($eventManager);
        return $this;
    }

    public function setConfigBinder(ConfigBinderInterface $configBinder) : ServiceRegistry {
        $this->configBinder = $configBinder;
        $this->manager->setConfigBinder($configBinder);
        return $this;
    }

    public function getService($interface) {
        return $this->manager->getService($interface);
    }

    public function getServices($interface): Collection {
        return $this->manager->getServices($interface);
    }

    public function getServicesByQuery(ServiceQuery $query): Collection {
        return $this->manager->getServicesByQuery($query);
    }

    public function getServicesByReference(ServiceReference $reference): Collection {
        return $this->manager->getServicesByReference($reference);
    }

    public function loadBundle(BundleManifest $manifest) {
        $bundleReader = $manifest->getReader();
        if ($bundleReader === null) {
            // @todo mark as incomplete or something
            return;
        }

        $svConfigRaw = $bundleReader->loadConfiguration(BundleReader::FILE_SERVICE_REGISTRY);
        $svcConfig = new ServiceRegistryConfig($svConfigRaw ?: []);
        $this->loadFromConfig($svcConfig, $manifest);
    }

    public function loadFromConfig(ServiceRegistryConfig $registryConfig, BundleManifest $manifest) {
        foreach ($registryConfig->getServiceConfigs() as $config) {
            $this->manager->startService($config, $manifest);
        }
    }

    public function unloadFromConfig(ServiceRegistryConfig $registryConfig) {
        foreach ($registryConfig->getServiceConfigs() as $config) {
            //$this->unregisterService($config);
        }
    }
}
