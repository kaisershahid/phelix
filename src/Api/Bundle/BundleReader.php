<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Filesys\Path;

/**
 * Wraps reading and loading bundle information.
 */
interface BundleReader {
    const FILE_SERVICE_REGISTRY = 'phelix/service-registry.yml';
    const FILE_MANIFEST = 'phelix/manifest.yml';

    public function setRoot(string $root) : BundleReader;

    public function loadManifest() : ?BundleManifest;

    public function loadConfiguration($fullPath) : ?array;
}
