<?php
namespace DinoTech\Phelix\Api\Bundle\Loaders;

use DinoTech\Phelix\Api\Bundle\BundleReader;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Filesys\Path;
use PHPUnit\Util\FileLoader;

class DetectBootable extends FilesysLoader {
    public function scan() : FilesysLoader {
        print_r($this->framework->getConfiguration()->jsonSerialize());
        $roots = $this->framework->getConfiguration()->getBundlesBoot();
        foreach ($roots as $dir) {
            $bootPath = Path::join($this->framework->getRoot(), $dir);
            Framework::debug("detect bootable: scan: $bootPath");
            if (!is_dir($bootPath)) {
                continue;
            }

            $this->scanDir($bootPath);
        }

        return $this;
    }

    public function scanDir(string $bootPath, int $depth = 0) {
        $dirs = dir($bootPath);
        $dir = $dirs->read();
        while ($dir) {
            if ($this->isValidDir($bootPath, $dir)) {
                $this->loadOrScan("$bootPath/$dir", $depth + 1);
            }

            $dir = $dirs->read();
        }
    }

    /**
     * @param $path
     * @param $depth
     */
    public function loadOrScan(string $path, int $depth) {
        if (!$this->loadFromPath($path) && $depth < self::MAX_DIR_DEPTH) {
            $this->scanDir($path, $depth);
        }
    }

    public function getManifests() : ListCollection {
        return $this->manifests;
    }
}
