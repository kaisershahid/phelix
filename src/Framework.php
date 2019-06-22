<?php
namespace DinoTech\Phelix;

use DinoTech\Phelix\Api\Bundle\BundleRegistry;
use DinoTech\Phelix\Api\Config\FileMatcher;
use DinoTech\Phelix\Api\Config\GenericConfig;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\StdLib\Filesys\Path;

/**
 * Coordinates initial configuration and booting, and provides read-only access
 * to the service registry.
 */
class Framework {
    private static $instance;

    const DEFAULT_ENV = 'local';
    const FILE_CONFIG = 'phelix-framework.yml';

    /**
     * Gets the singleton instance. If not yet created, uses the supplied environment
     * to create the singleton.
     * @param null $env
     * @return Framework
     */
    public static function getInstance($env = null) {
        if (self::$instance === null) {
            self::$instance = new self($env ?: self::DEFAULT_ENV);
        }

        return self::$instance;
    }

    public static $debugEnabled = false;

    public static function debug(string $str, $extra = null) {
        if (self::$debugEnabled) {
            error_log("[phelix] $str");
        }
    }

    /** @var Env */
    private $env;
    /** @var string */
    private $root;
    /** @var string */
    private $configFile;
    /** @var array */
    private $configuration;
    /** @var bool */
    private $booted = false;

    /** @var BundleRegistry */
    private $bundleRegistry;
    /** @var ServiceRegistry */
    private $serviceRegistry;
    /** @var  */
    private $eventListeners;

    public function __construct(string $env = 'local') {
        $this->env = new Env($env);
        $this->root = getcwd();
    }

    /**
     * @return mixed
     */
    public function getRoot() {
        return $this->root;
    }

    /**
     * @param mixed $root
     * @return Framework
     */
    public function setRoot($root) {
        $this->exceptionIfBooted("cannot set configFile after boot");
        $this->root = $root;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfigFile() {
        return $this->configFile;
    }

    /**
     * @param string $configFile
     * @return Framework
     */
    public function setConfigFile(string $configFile) {
        $this->exceptionIfBooted("cannot set configFile after boot");
        $this->configFile = $configFile;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration() {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @return Framework
     */
    public function setConfiguration(array $configuration) {
        $this->exceptionIfBooted("cannot set configFile after boot");
        $this->configuration = $configuration;
        return $this;
    }

    protected function exceptionIfBooted(string $message) {
        if ($this->booted) {
            throw new FrameworkException($message);
        }
    }

    public function boot() {
        if ($this->booted) {
            return;
        }

        // @todo make a FrameworkLoader pattern so that we can leverage startup from build/cache/whatever
        $this->chooseAndLoadConfig();
        $this->loadBundles();
        $this->startBundles();
        $this->booted = true;
    }

    public function isBooted() {
        return $this->booted;
    }

    protected function chooseAndLoadConfig() {
        if ($this->configuration !== null) {
            self::debug("framework: configuration set");
            return;
        }

        $this->root = $this->root ?: getcwd();
        $path = Path::joinAndNormalize($this->root, $this->configFile);
        try {
            self::debug("framework: configuration from file: $path");
            $this->configuration = (new GenericConfig())->loadYamlFromFile($path);
            $this->loadAndMergeConfigsByEnvironment();
        } catch (\RuntimeException $e) {
            throw new FrameworkException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function loadAndMergeConfigsByEnvironment() {
        // @todo move config logic to FrameworkConfig or something
        $fm = new FileMatcher($this->configFile, $this->root);
        $confSuffixes = $fm->getMatchingSuffixes();
        foreach ($confSuffixes as $confSuffix) {
            if ($this->env->is($confSuffix)) {
                //$this->mergeConfig($fm->getFullPathBySuffix($confSuffix));
            }
        }
    }

    protected function loadBundles() {

    }

    protected function startBundles() {

    }
}
