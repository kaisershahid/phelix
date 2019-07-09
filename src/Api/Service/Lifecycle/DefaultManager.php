<?php
namespace DinoTech\Phelix\Api\Service\Lifecycle;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Config\FrameworkConfig;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Event\Defaults\EventManager;
use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\Registry\Scoreboard;
use DinoTech\Phelix\Api\Service\Registry\ServiceTracker;
use DinoTech\Phelix\Api\Service\Registry\TrackerKeyValue;
use DinoTech\Phelix\Api\Service\ServiceEventTopics;
use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\Phelix\Api\Service\ServiceRegistryInterface;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\KeyValue;

/**
 * The bootstrap lifecycle manager that is fully dynamic and automated in service
 * activation and deactivation (e.g. not dependent on any caching or service
 * ordering).
 *
 * @todo make an interface LifecycleManager
 * @todo track service activation order to provide insights into optimizing for other managers
 * @todo ensure service list returns by rank (or allows retrieving highest rank)
 */
class DefaultManager implements ServiceRegistryInterface {
    /** @var EventManagerInterface */
    private $eventManager;
    /** @var Index */
    private $services;
    /** @var Scoreboard */
    private $depScoreboard;
    /** @var Activator */
    private $activator;
    /** @var Deactivator */
    private $deactivator;

    public function __construct(Index $services) {
        $this->services = $services;
        $this->depScoreboard = Scoreboard::makeForCardinality();
        $this->eventManager = new EventManager();
        $this->activator = new Activator($this->services, $this->eventManager);
        $this->deactivator = new Deactivator($this->services, $this->eventManager);
    }

    /**
     * @param EventManagerInterface $eventManager
     * @return DefaultManager
     */
    public function setEventManager(EventManagerInterface $eventManager): DefaultManager {
        $this->eventManager = $eventManager;
        return $this;
    }

    public function getService($interface) {
        $tracker = $this->getServiceTracker($interface);
        if ($tracker !== null) {
            return $tracker->getComponent();
        }

        return null;
    }

    public function getServiceTracker($interface) : ServiceTracker {
        /** @var ServiceTracker[]|Collection $services */
        $services = $this->getServices($interface);
        if ($services->count() > 0) {
            return $services[0];
        }

        return null;
    }

    public function getServices($interface): Collection {
        return $this->getServicesByQuery(ServiceQuery::fromInterface($interface));
    }

    public function getServicesByReference(ServiceReference $reference): Collection {
        return $this->getServicesByQuery(ServiceQuery::fromReference($reference));
    }

    public function getServicesByQuery(ServiceQuery $query): Collection {
        $services = $this->services->search($query);
        if ($services === null) {
            return new StandardList();
        }

        $activator = $this->activator;
        return $services->getTrackersByRank()
            ->filter(function(KeyValue $kv) use ($activator) {
                /** @var ServiceTracker $t */
                $t = $kv->value();
                if ($t->getStatus() === LifecycleStatus::SATISFIED()) {
                    $activator->activateIfReferencesSatisfied($t, true);
                }

                return $t->getStatus()->greaterThanOrEqual(LifecycleStatus::SATISFIED());
            });
    }

    public function startService(ServiceConfig $config, BundleManifest $manifest) {
        $class = $config->getClass();
        if (!class_exists($class)) {
            throw new \RuntimeException("MAKE SPECIFIC: class not found: $class");
        }

        $component = new $class();
        $tracker = new ServiceTracker($component, $config, $manifest);
        $tracker->setIntrospector(new Introspector($component));
        $this->services->add($tracker);
        $this->activator->activate($tracker);
    }

    public function stopService(ServiceTracker $tracker) {
        $this->deactivator->deactivate($tracker);
    }
}
