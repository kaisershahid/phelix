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

    public function loadManifest() : BundleReader {
        $path = Path::join($this->root, self::FILE_MANIFEST);
        Framework::debug("loadManifest: $path");
        if (file_exists($path)) {
            $manifest = (new GenericConfig())->noCallbacks()
                ->loadYamlFromFile($path);
            $this->manifest = new BundleManifest($this->root, $manifest);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getManifest() : BundleManifest {
        return $this->manifest;
    }

    public static function isDirBundle(string $path) : bool {
        $manifest = Path::join($path, self::FILE_MANIFEST);
        return file_exists($manifest);
    }
}
