<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\StdLib\Collections\ArrayUtils as Arr;

class BundleManifest implements \JsonSerializable {
    const KEY_GROUP_ID = 'groupId';
    const KEY_BUNDLE_ID = 'bundleId';
    const KEY_VERSION = 'version';
    const KEY_NAMESPACE = 'namespace';
    const KEY_ACTIVATOR = 'activator';
    const KEY_REQUIRES = 'requires';
    const KEY_SRC_ROOT = 'srcRoot';
    const KEY_RESOURCE_ROOT = 'resourceRoot';

    const DEFAULT_SRC_ROOT = 'src';
    const DEFAULT_RESOURCE_ROOT = 'resources';

    /** @var string */
    private $bundleRoot;
    /** @var string */
    private $groupId;
    /** @var string */
    private $bundleId;
    /** @var string */
    private $version;
    /** @var string */
    private $namespace;
    /** @var string */
    private $srcRoot;
    /** @var string */
    private $resourceRoot;
    /** @var string */
    private $activator;
    /** @var array */
    private $requires;

    public function __construct(string $root, array $config) {
        $this->bundleRoot = $root;
        $this->groupId = Arr::get($config, self::KEY_GROUP_ID);
        $this->bundleId = Arr::get($config, self::KEY_BUNDLE_ID);
        $this->version = Arr::get($config, self::KEY_VERSION);
        $this->namespace = Arr::get($config, self::KEY_NAMESPACE);
        $this->srcRoot = Arr::get($config, self::KEY_SRC_ROOT, self::DEFAULT_SRC_ROOT);
        $this->resourceRoot = Arr::get($config, self::KEY_RESOURCE_ROOT, self::DEFAULT_RESOURCE_ROOT);
        $this->activator = Arr::get($config, self::KEY_ACTIVATOR);
        $this->requires = Arr::get($config, self::KEY_REQUIRES, []);
    }

    /**
     * @return string
     */
    public function getBundleRoot(): string {
        return $this->bundleRoot;
    }

    /**
     * @return string
     */
    public function getGroupId(): string {
        return $this->groupId;
    }

    /**
     * @return string
     */
    public function getBundleId(): string {
        return $this->bundleId;
    }

    /**
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getSrcRoot(): string {
        return $this->srcRoot;
    }

    /**
     * @return string
     */
    public function getResourceRoot(): string {
        return $this->resourceRoot;
    }

    /**
     * @return string
     */
    public function getActivator(): ?string {
        return $this->activator;
    }

    /**
     * @return array
     */
    public function getRequires(): array {
        return $this->requires;
    }

    public function getId() : string {
        return "{$this->groupId}/{$this->bundleId}";
    }

    public function jsonSerialize() {
        return [
            self::KEY_GROUP_ID => $this->groupId,
            self::KEY_BUNDLE_ID => $this->bundleId,
            self::KEY_VERSION => $this->version,
            self::KEY_NAMESPACE => $this->namespace,
            self::KEY_ACTIVATOR => $this->activator,
            self::KEY_SRC_ROOT => $this->srcRoot,
            self::KEY_RESOURCE_ROOT => $this->resourceRoot,
            self::KEY_REQUIRES => $this->requires
        ];
    }
}
