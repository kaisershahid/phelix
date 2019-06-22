<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Api\Config\GenericConfig;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Filesys\Path;

/**
 * @todo make BundleManifest
 * @todo make ServiceRegistryConfig?
 */
class BundleLoader {
    const DIR_CONFIG = 'config/';
    const FILE_SERVICE_REGISTRY = 'service-registry.yml';
    const FILE_MANIFEST = 'phelix.yml';

    protected $root;
    protected $activator;
    protected $manifest;
    protected $serviceRegistryConfig = [];

    public function __construct(string $root) {
        $this->root = $root;
    }

    public function load(ServiceRegistry $registry) {
        $this->loadManifest();
        $this->loadServiceRegistryConfig();
    }

    public function loadManifest() {
        $path = Path::joinAndNormalize($this->root, self::FILE_MANIFEST);
        Framework::debug("loadManifest: $path");
        if (!file_exists($path)) {
            return;
        }

        $this->manifest = (new GenericConfig())->noCallbacks()->loadYamlFromFile($path);
    }

    protected function loadServiceRegistryConfig() {
        $path = Path::joinAndNormalize($this->root, self::DIR_CONFIG, self::FILE_SERVICE_REGISTRY);
        Framework::debug("laodServiceRegistryConfig: $path");
        if (!file_exists($path)) {
            return;
        }

        $this->serviceRegistryConfig = (new GenericConfig())->loadYamlFromFile($path);
    }

    /**
     * @return mixed
     */
    public function getManifest() {
        return $this->manifest;
    }

    /**
     * @return array
     */
    public function getServiceRegistryConfig(): array {
        return $this->serviceRegistryConfig;
    }
}
