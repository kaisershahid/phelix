<?php
namespace DinoTech\Phelix\Api\Bundle\Loaders;

use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Filesys\Path;
use DinoTech\StdLib\KeyValue;

class DetectNamedLibs extends DetectBootable {
    /**
     * For each defined root, check that the defined bundles exist and load whenever
     * found.
     * @return DetectBootable
     */
    public function scan(): FilesysLoader {
        $roots = $this->framework->getConfiguration()->getBundleRoots();
        $bundles = $this->framework->getConfiguration()->getBundles();

        foreach ($bundles as $bundleDef) {
            $bundlePath = self::turnDefToDirPath($bundleDef);
            $this->lookupAndAddBundle($roots, $bundlePath);
        }

        return $this;
    }

    public function lookupAndAddBundle(Collection $roots, string $bundlePath) {
        foreach ($roots as $root) {
            $path = Path::join($this->framework->getRoot(), $root, $bundlePath);
            echo ">> $path <<\n";
            if ($this->loadFromPath($path)) {
                Framework::debug("named lib: loaded $bundlePath from $path");
                return;
            }
        }

        Framework::debug("named lib: not found: $bundlePath");
    }

    public static function turnDefToDirPath(string $str) : string {
        $parts = preg_split('#[/@]#', $str, 3);
        $group = array_shift($parts);
        $bundle = array_shift($parts);
        $version = array_shift($parts);
        return "$group/$bundle" . ($version ? "-$version" : '');
    }
}
