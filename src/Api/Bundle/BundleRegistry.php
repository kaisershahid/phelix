<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Api\Event\Defaults\Event;
use DinoTech\Phelix\Api\Event\Defaults\EventManager;
use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\MapCollection;
use DinoTech\StdLib\Collections\StandardMap;
use DinoTech\StdLib\Filesys\Path;
use DinoTech\StdLib\KeyValue;

/**
 * Tracks all loaded manifests and is responsible for starting/stopping bundles.
 */
class BundleRegistry {
    /** @var BundleManifest[]|MapCollection */
    private $manifests;
    /** @var BundleTracker[]|MapCollection */
    private $records;
    /** @var Framework */
    private $framework;
    /** @var ServiceRegistry */
    private $serviceRegistry;
    /** @var EventManagerInterface */
    private $eventManager;

    public function __construct() {
        $this->manifests = new StandardMap();
        $this->records = new StandardMap();
        $this->eventManager = new EventManager();
    }

    /**
     * @param Framework $framework
     * @return BundleRegistry
     */
    public function setFramework(Framework $framework): BundleRegistry {
        $this->framework = $framework;
        return $this;
    }

    /**
     * @param ServiceRegistry $serviceRegistry
     * @return BundleRegistry
     */
    public function setServiceRegistry(ServiceRegistry $serviceRegistry): BundleRegistry {
        $this->serviceRegistry = $serviceRegistry;
        return $this;
    }

    /**
     * @param EventManagerInterface $eventManager
     * @return BundleRegistry
     */
    public function setEventManager(EventManagerInterface $eventManager): BundleRegistry {
        $this->eventManager = $eventManager;
        return $this;
    }

    public function registerBundles(Collection $bmanifests) {
        $bmanifests->traverse(function(KeyValue $kv) {
            $this->registerBundle($kv->value());
        });
    }

    public function registerBundle(BundleManifest $manifest) {
        // @todo check dependencies -- if not all found, put in wait queue
        // @todo if group id/bundle id exist...?
        $id = $manifest->getId();
        $record = (new BundleTracker())
            ->setManifest($manifest)
            ->setStatus(BundleStatus::REGISTERED());
        $this->eventManager->dispatch(BundleEventTopics::REGISTERED, $manifest);

        $this->manifests[$id] = $manifest;
        $this->records[$id] = $record;
        // @todo after activated, check if this resolves other deps in queue
    }

    public function startBundles() {
        $this->records->traverse(function(KeyValue $kv) {
            $this->startBundle($kv->key());
        });
    }

    protected function startBundle(string $id) {
        $record = $this->records[$id];
        $manifest = $record->getManifest();
        $activator = $this->checkForSourceRootAndActivator($manifest);
        if (!$activator) {
            $activator = new DefaultActivatorInterface();
        }

        $record->setActivator($activator);
        try {
            $activator
                ->setFramework($this->framework)
                ->setManifest($manifest)
                ->activate($this->serviceRegistry);
            $record->setStatus(BundleStatus::ACTIVE());
            // @todo make BundleEvent
            $this->eventManager->dispatch(BundleEventTopics::ACTIVATED, $manifest);
        } catch (\Exception $e) {
            error_log("ERROR: activating bundle $id: {$e->getMessage()}\n{$e->getTraceAsString()}");
            $record
                ->setStatus(BundleStatus::ERROR())
                ->setLifecycleException($e);
            $this->eventManager->dispatch(BundleEventTopics::ERROR, $e);
        }
    }

    protected function checkForSourceRootAndActivator(BundleManifest $manifest) : ?BundleActivatorInterface {
        $bundleRoot = $manifest->getBundleRoot();
        $srcDir = $manifest->getSrcRoot();

        // @todo check srcRoot
        $srcRoot = Path::join($bundleRoot, $srcDir);
        $namespace = $manifest->getNamespace();
        Framework::registerNamespace($namespace, $srcRoot);

        $activatorName = $manifest->getActivator();
        if ($activatorName) {
            // @todo check if class exists and mark bundle as error if not valid
            $class = $namespace . '\\' . $activatorName;
            return new $class();
        }

        return null;
    }

    public function stopBundles() {
        $this->records->traverse(function(KeyValue $kv) {
            $this->stopBundle($kv->key());
        });
    }

    protected function stopBundle(string $id) {
        $record = $this->records[$id];
        try {
            $this->eventManager->dispatch(BundleEventTopics::DEACTIVATING, $record->getManifest());
            $record->getActivator()->deactivate($this->serviceRegistry);
        } catch (\Exception $e) {
            error_log("ERROR: deactivating bundle $id: {$e->getMessage()}\n{$e->getTraceAsString()}");
            // @todo ERROR_DEACTIVATION topic?
        }
    }
}
