<?php
namespace DinoTech\Phelix;

use DinoTech\Phelix\Api\Bundle\BundleRegistry;
use DinoTech\Phelix\Api\Config\FileMatcher;
use DinoTech\Phelix\Api\Config\Loaders\FrameworkConfigLoader;
use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Api\Config\Wrappers\FrameworkConfig;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\StdLib\Filesys\Path;

/**
 * Coordinates initial configuration and booting, and provides read-only access
 * to the service registry.
 */
class Framework {
    private static $instance;

    const DEFAULT_ENV         = 'dev';
    const DEFAULT_FILE_CONFIG = 'phelix-framework.yml';

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
    private $configFile = self::DEFAULT_FILE_CONFIG;
    /** @var FrameworkConfig */
    private $configuration;
    /** @var bool */
    private $booted = false;

    /** @var BundleRegistry */
    private $bundleRegistry;
    /** @var ServiceRegistry */
    private $serviceRegistry;
    /** @var  */
    private $eventListeners;

    public function __construct(string $env = self::DEFAULT_ENV) {
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
    public function setRoot($root) : Framework {
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
    public function setConfigFile(string $configFile) : Framework {
        $this->exceptionIfBooted("cannot set configFile after boot");
        $this->configFile = $configFile;
        return $this;
    }


    /**
     * @param array $configuration
     * @return Framework
     */
    public function setConfiguration(FrameworkConfig $configuration) : Framework {
        $this->exceptionIfBooted("cannot set configuration after boot");
        $this->configuration = $configuration;
        return $this;
    }

    public function getConfiguration() : FrameworkConfig {
        return $this->configuration;
    }

    protected function exceptionIfBooted(string $message) {
        if ($this->booted) {
            throw new FrameworkException($message);
        }
    }

    public function boot() : Framework {
        if ($this->booted) {
            return $this;
        }

        // @todo make a FrameworkLoader pattern so that we can leverage startup from build/cache/whatever
        $this->loadConfig();
        $this->loadBundles();
        $this->startBundles();
        $this->booted = true;
        return $this;
    }

    public function isBooted() {
        return $this->booted;
    }

    protected function loadConfig() {
        if ($this->configuration !== null) {
            self::debug("framework: configuration explicitly set");
            return;
        }

        $this->root = $this->root ?: getcwd();

        try {
            $this->configuration = (new FrameworkConfigLoader($this->root, $this->configFile))
                ->setEnvironment($this->env)
                ->loadAndMergeConfigs();
        } catch (\RuntimeException $e) {
            throw new FrameworkException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function loadBundles() {

    }

    protected function startBundles() {

    }
}