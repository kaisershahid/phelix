<?php
namespace DinoTech\Phelix\Api\Config\Binders;

use DinoTech\Phelix\Api\Config\ConfigBinderInterface;
use DinoTech\Phelix\Api\Config\Loaders\DirMatcher;
use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Api\Config\ServiceProperties;
use DinoTech\Phelix\Env;
use DinoTech\StdLib\NameConverter;

/**
 * Given a service ID, attempts to find the best matching configuration based on
 * environment tag. For instance, if our environment is set to `dev.local`, and
 * we have the following directories under our config binding root:
 *
 * - `default`
 * - `dev`
 * - `dev.local`,
 * - `dev.local.something`
 *
 * The directories will be searched in that same order and the deepest match
 * will be kept.
 *
 * ## File Naming
 *
 * Service properties follow the naming convention of `Company.Package.Service.yml`.
 *
 * Note that service IDs derived directly from a class (e.g. `Company\\Package\Service``)
 * will be converted to `Company.Package.Service`.
 *
 * ## File Organization
 *
 * Within each environment directory, the configuration can be broken up along
 * namespaces, shifting off the corresponding component from the ID. Examples:
 *
 * - `Company.Package.Service.yml`
 * - `Company/Package.Service.yml`
 * - `Company/Package/Service.yml`
 *
 * Note that the first match will be used, not the deepest. The default max depth
 * to check is 2.
 */
class Filesys implements ConfigBinderInterface {
    const DIR_PREFIX = 'bindings';

    /** @var string */
    private $root;
    /** @var array */
    private $dirs;
    /** @var Env */
    private $env;
    /** @var DirMatcher */
    private $dirMatcher;

    public function __construct(string $root, Env $env = null) {
        $this->env = $env ?: new Env('');
        $this->setRoot($root);
    }

    public function setRoot(string $root) : Filesys {
        $this->root = $root;
        $this->dirs = array_merge(['default'], $this->getSubDirs());
        return $this;
    }

    protected function getSubDirs() : array {
        $dirs = [];
        $entries = glob("{$this->root}/*");
        $len = strlen($this->root) + 1;
        foreach ($entries as $path) {
            $entry = substr($path, $len);
            if ($entry === 'default') {
                continue;
            }

            if (is_dir($path)) {
                $dirs[] = $entry;
            }
        }

        usort($dirs, function(string $a, string $b) {
            $alen = count(explode('.', $a));
            $blen = count(explode('.', $b));
            if ($alen > $blen) {
                return 1;
            } else if ($alen < $blen) {
                return 0;
            } else {
                if ($a > $b) {
                    return 1;
                } else if ($a < $b) {
                    return -1;
                }
            }

            return 0;
        });

        return array_filter($dirs, function(string $dir) {
            return $this->env->is($dir);
        });
    }

    public function getDirs() : array {
        return $this->dirs;
    }

    public function getConfigBinding(string $serviceId): ServiceProperties {
        $idNorm = NameConverter::classToDot($serviceId);
        $candidate = $this->resolveConfigPath($idNorm);

        $props = null;
        if ($candidate) {
            $props = (new GenericConfig())
                ->noCallbacks()
                ->loadYamlFromFile($candidate);
        }

        return new ServiceProperties($props ?: []);
    }

    public function resolveConfigPath(string $id) : ?string {
        // @todo make depth configurable
        $candidate = array_reduce($this->dirs, function($carry, string $dir) use ($id) {
            $path = $this->resolveConfigSubPath("{$this->root}/$dir", $id, 2);
            return $path ?: $carry;
        }, null);

        return $candidate;
    }

    /**
     * Recursively checks that a configuration for a service ID exists by:
     *
     * - shifting the next ID component to a directory
     * - checking the remaining ID components are within that directory
     *
     * Recursion stops at first valid file.
     *
     * @param string $root
     * @param string $id
     * @param int $depth The number of directories to go down. `-1` will check
     * all possible directories.
     * @return string|null
     */
    public function resolveConfigSubPath(string $root, string $id, $depth = -1) {
        $idParts = explode('.', $id);
        while (!empty($idParts)) {
            $path = "$root/$id.yml";
            if (file_exists($path)) {
                return $path;
            }

            $root .= '/' . array_shift($idParts);
            $id = implode('.', $idParts);
            $depth--;
            if ($depth === -1) {
                break;
            }
        }

        return null;
    }
}
