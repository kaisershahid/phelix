<?php
namespace DinoTech\Phelix\Api\Bundle\Loaders;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Bundle\BundleReader;
use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Filesys\Path;

class FilesysReader implements BundleReader {
    protected $root;
    /** @var BundleManifest */
    protected $manifest;
    /** @var array */
    protected $serviceRegistryConfig = [];

    public function setRoot(string $root) : BundleReader {
        $this->root = $root;
        return $this;
    }

    public function loadManifest() : ?BundleManifest {
        Framework::debug("loadManifest: from {$this->root}");
        $raw = $this->loadConfiguration(self::FILE_MANIFEST);
        if ($raw !== null) {
            $this->manifest = new BundleManifest($this->root, $raw, $this);
            return $this->manifest;
        }

        return null;
    }

    /**
     * @return BundleManifest
     */
    public function getManifest() : BundleManifest {
        return $this->manifest;
    }

    public function loadConfiguration($path): ?array {
        $fullPath = Path::join($this->root, $path);
        if (file_exists($fullPath)) {
            return (new GenericConfig())->noCallbacks()
                ->loadYamlFromFile($fullPath);
        }

        return null;
    }

    public static function isDirBundle(string $path) : bool {
        $manifest = Path::join($path, self::FILE_MANIFEST);
        return file_exists($manifest);
    }
}
