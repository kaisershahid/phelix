<?php
namespace DinoTech\Phelix\Api\Config\Wrappers;

use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\MapCollection;
use DinoTech\StdLib\Collections\ReadOnlyCollection;
use DinoTech\StdLib\Collections\ReadOnlyMap;
use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;
use DinoTech\StdLib\Collections\Traits\MapCollectionTrait;
use DinoTech\StdLib\Collections\UnsupportedOperationException;

/**
 * @todo make readonly
 * @todo make merge
 */
class FrameworkConfig implements MapCollection {
    use MapCollectionTrait;
    use CollectionTrait;
    use ArrayAccessTrait;
    use IteratorTrait;
    use CountableTrait;

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

    /** @var MapCollection */
    protected $properties;
    /** @var MapCollection */
    protected $framework;
    /** @var Collection */
    protected $bundlesBoot;
    /** @var Collection */
    protected $bundleRoots;
    /** @var Collection */
    protected $bundles;

    public static function makeWithDefaults(array $config = []) {
        $merged = array_merge_recursive($config, self::DEFAULTS);
        return new self($merged);
    }

    protected $arr;

    public function __construct(array $config) {
        $this->arr = $config;
        $this->properties = new ReadOnlyMap(ArrayUtils::get($this->arr, self::KEY_PROPERTIES, []));
        $this->framework = new ReadOnlyMap(ArrayUtils::get($this->arr, self::KEY_FRAMEWORK, []));
        $this->bundleRoots = new ReadOnlyCollection(ArrayUtils::get($this->arr, self::KEY_BUNDLE_ROOTS, []));
        $this->bundlesBoot = new ReadOnlyCollection(ArrayUtils::get($this->arr, self::KEY_BUNDLES_BOOT, []));
        $this->bundles = new ReadOnlyCollection(ArrayUtils::get($this->arr, self::KEY_BUNDLES, []));
    }

    public function clear() : Collection {
        throw new UnsupportedOperationException("not allowed");
    }

    public function getProperties() : MapCollection {
        return $this->properties;
    }

    public function getFramework() : MapCollection {
        return $this->framework;
    }

    public function getBundlesBoot() : Collection {
        return $this->bundlesBoot;
    }

    public function getBundleRoots() : Collection {
        return $this->bundleRoots;
    }

    public function getBundles() : Collection {
        return $this->bundles;
    }

    public function mergeToNew(array $cfg) : FrameworkConfig {
        return new FrameworkConfig(
            array_merge_recursive($this->arr, $cfg)
        );
    }

    public static function processIncludesKeys(array $config) {
        $newConfig = [];
        foreach ($config as $key => $val) {

        }
    }
}
