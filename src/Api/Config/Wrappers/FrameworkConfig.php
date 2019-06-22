<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardMap;

class FrameworkConfig {
    const KEY_PROPERTIES = 'properties';
    const KEY_FRAMEWORK = 'framework';
    const KEY_BUNDLES_BOOT = 'bundlesBoot';
    const KEY_BUNDLE_ROOTS = 'bundleRoots';
    const KEY_BUNDLES = 'bundles';

    const DEFAULTS = [
        'properties' => [],
        'framework' => [
            'path.tmp' => './var/tmp',
            'path.log' => './var/log',
            'path.cache' => './var/cache'
        ],
        'bundlesBoot' => [
            './bundles'
        ],
        'bundleRoots' => [
            './vendor'
        ],
        'bundles' => []
    ];

    private $properties = [];
    private $framework = self::DEFAULTS['framework'];
    private $bundlesBoot = self::DEFAULTS['bundlesBoot'];
    private $bundleRoots = self::DEFAULTS['bundleRoots'];
    private $bundles = [];

    public function getProperties() : StandardMap {
        return new StandardMap($this->properties);
    }

    public function getFramework() : StandardMap {
        return new StandardMap($this->framework);
    }

    public function getBundlesBoot() : StandardList {
        return new StandardList($this->bundlesBoot);
    }

    public function getBundleRoots() : StandardList {
        return new StandardList($this->bundleRoots);
    }

    public function getBundles() : StandardList {
        return new StandardList($this->bundles);
    }
}
