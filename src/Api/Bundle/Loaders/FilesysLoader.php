<?php
namespace DinoTech\Phelix\Api\Bundle\Loaders;

use DinoTech\Phelix\Api\Bundle\BundleReader;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Filesys\Path;

abstract class FilesysLoader {
    const MAX_DIR_DEPTH = 2;
    /** @var Framework */
    protected $framework;
    /** @var ListCollection */
    protected $manifests;

    public function __construct(Framework $framework) {
        $this->framework = $framework;
        $this->manifests = new StandardList();
    }

    abstract public function scan() : FilesysLoader;

    public function isValidDir($root, $dir) {
        return $dir != '.' && $dir != '..' && is_dir("$root/$dir");
    }

    protected function loadFromPath($path) : bool {
        if (!FilesysReader::isDirBundle($path)) {
            return false;
        }

        $reader = (new FilesysReader())->setRoot($path);
        $manifest = $reader->loadManifest();
        $this->manifests->push($manifest);

        Framework::debug("filesys loader: manifest found in $path");
        return true;
    }

    public function getManifests() : ListCollection {
        return $this->manifests;
    }
}
