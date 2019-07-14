<?php
namespace DinoTech\Phelix;

use DinoTech\Phelix\Api\Bundle\BundleRegistry;
use DinoTech\Phelix\Api\Bundle\Loaders\DetectBootable;
use DinoTech\Phelix\Api\Bundle\Loaders\DetectNamedLibs;
use DinoTech\Phelix\Api\Config\Binders\FilesysBinder;
use DinoTech\Phelix\Api\Config\ConfigBinderInterface;
use DinoTech\Phelix\Api\Config\Loaders\FileMatcher;
use DinoTech\Phelix\Api\Config\Loaders\FrameworkConfigLoader;
use DinoTech\Phelix\Api\Config\Loaders\GenericConfig;
use DinoTech\Phelix\Api\Config\FrameworkConfig;
use DinoTech\Phelix\Api\Event\Defaults\EventManager;
use DinoTech\Phelix\Api\Event\EventHandlerInterface;
use DinoTech\Phelix\Api\Event\EventInterface;
use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\StdLib\Filesys\Path;

/**
 * Coordinates initial configuration and booting, and provides read-only access
 * to the service registry.
 */
class Framework implements EventManagerInterface{
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
    public static $debugFunc = 'error_log';

    public static function debug(string $str, $extra = null) {
        if (self::$debugEnabled) {
            $func = self::$debugFunc;
            $func("[phelix] $str");
        }
    }

    private static $namespaces = [];
    private static $autoloaderRegistered = false;

    public static function registerNamespace($namespace, $root) {
        if (!isset(self::$namespaces[$namespace])) {
            self::$namespaces[$namespace] = $root;
        }
    }

    public static function registerAutoloader() {
        if (self::$autoloaderRegistered) {
            return;
        }

        self::$autoloaderRegistered = true;
        spl_autoload_register([self::class, 'autoloader']);
    }

    public static function autoloader($class) {
        foreach (self::$namespaces as $namespace => $root) {
            $ns = $namespace . '\\';
            $pos = strpos($class, $ns);
            if ($pos === 0) {
                $file = substr($class, strlen($ns)) . '.php';
                $path = Path::join($root, $file);
                if (file_exists($path)) {
                    require $path;
                    return true;
                }
            }
        }

        return false;
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
    /** @var EventManagerInterface */
    private $eventManager;
    /** @var ConfigBinderInterface */
    private $configBinder;

    public function __construct(string $env = self::DEFAULT_ENV) {
        $this->env = new Env($env);
        $this->root = getcwd();
        $this->configBinder = new FilesysBinder(Path::join($this->root, 'configs'), $this->env);
        $this->eventManager = new EventManager();
        $this->serviceRegistry = (new ServiceRegistry())
            ->setEventManager($this->eventManager)
            ->setConfigBinder($this->configBinder);
        $this->bundleRegistry = (new BundleRegistry())
            ->setFramework($this)
            ->setServiceRegistry($this->serviceRegistry)
            ->setEventManager($this->eventManager);
        register_shutdown_function(function() {
            $this->shutdown();
        });
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
        $this->configBinder->setRoot($root);
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
        $configPath = $this->configuration->getFramework()['path.config'];
        $this->configBinder
            ->setRoot(Path::joinIfRelative($configPath, $this->root));

        $this->loadBundles();
        $this->bundleRegistry->startBundles();

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
        $bootManifests = (new DetectBootable($this))->scan()->getManifests();
        $libManifests = (new DetectNamedLibs($this))->scan()->getManifests();
        $this->bundleRegistry->registerBundles($bootManifests->addAll($libManifests));
    }

    public function getService($interface) {
        return $this->serviceRegistry->getService($interface);
    }

    public function getServiceByQuery(ServiceQuery $query) {
        return $this->serviceRegistry->getServicesByQuery($query)[0];
    }

    public function registerEventHandler(EventHandlerInterface $handler, array $topics) {
        $this->eventManager->registerEventHandler($handler, $topics);
    }

    public function unregisterEventHandler(EventHandlerInterface $handler) {
        $this->eventManager->unregisterEventHandler($handler);
    }

    public function dispatch($topic, $payload = null, $payloadType = null) {
        $this->eventManager->dispatch($topic, $payload, $payloadType);
    }

    public function dispatchEvent(EventInterface $event) {
        $this->eventManager->dispatchEvent($event);
    }

    protected function shutdown() {
        self::debug("shutdown() -- STARTING");
        $this->bundleRegistry->stopBundles();
        self::debug("shutdown() -- ENDED");
    }
}
