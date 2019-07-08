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

    public function __construct(Index $services) {
        $this->services = $services;
        $this->depScoreboard = Scoreboard::makeForCardinality();
        $this->eventManager = new EventManager();
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
        /** @var ServiceTracker[]|Collection $services */
        $services = $this->getServices($interface);
        if ($services->count() > 0) {
            return $services[0]->getComponent();
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

        return $services->getTrackersByRank()
            ->filter(function(KeyValue $kv) {
                /** @var ServiceTracker $t */
                $t = $kv->value();
                if ($t->getStatus() === LifecycleStatus::SATISFIED()) {
                    $this->activateIfReferencesSatisfied($t, true);
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
        $refs = $tracker->getUnmarkedReferences();
        if ($refs->count() > 0) {
            $this->resolveReferences($tracker);
        } else {
            $this->activateIfReferencesSatisfied($tracker);
        }
    }

    public function resolveReferences(ServiceTracker $tracker) {
        $refs = $tracker->getUnmarkedReferences();
        foreach ($refs as $ref) {
            $cardinality = $ref->getCardinality();
            $refQuery = $this->services->addReference($ref);
            Framework::debug("resolve ref({$refQuery->getHash()}) for {$tracker->getConfig()->getId()}");

            if ($cardinality->isMandatory()) {
                if ($refQuery->hasOneSatisfied()) {
                    $tracker->getRefScoreboard()->decrease($cardinality);
                    $tracker->markReference($ref);
                } else {
                    $refQuery->addDependent($tracker);
                }
            } else {
                $tracker->getRefScoreboard()->decrease($cardinality);
                $tracker->markReference($ref);
            }
        }

        $this->activateIfReferencesSatisfied($tracker);
    }

    public function activateIfReferencesSatisfied(ServiceTracker $tracker, $forceActivation = false) {
        if ($tracker->getStatus() == LifecycleStatus::ACTIVE()) {
            return;
        }

        if ($tracker->getRefScoreboard()->getTotalScore() == 0) {
            $tracker->setStatus(LifecycleStatus::SATISFIED());
            if ($tracker->getConfig()->getComponent()->isImmediate() || $forceActivation) {
                $this->activate($tracker);
            }
        } else {
            Framework::debug("unsatified: {$tracker->getConfig()->getId()}");
        }
    }

    public function activate(ServiceTracker $tracker) {
        if ($tracker->getStatus() === LifecycleStatus::ACTIVE()) {
            return;
        }

        // @todo catch exception
        (new ReferenceBinder($this->services, $tracker))->bind();

        // @todo resolve configuration & merge with metadata
        $activation = $tracker->getConfig()->getComponent()->getActivate();
        if ($activation) {
            // @todo move to invokeActivation method
            try {
                // @todo send ServiceProperties instead of metadata
                Framework::debug("activating {$tracker->getConfig()->getId()}");
                $tracker->getIntrospector()
                    ->invokeMethod($activation, $tracker->getConfig()->getMetadata());
                $tracker->setStatus(LifecycleStatus::ACTIVE());
                $this->updateDependents($tracker);
                // @todo make ServiceEvent
                $this->eventManager->dispatch(ServiceEventTopics::ACTIVATED, $tracker->getConfig());
            } catch (\Exception $e) {
                Framework::debug("activate(): {$e->getMessage()}");
                // @todo capture exception message in tracker & log
                $tracker->setStatus(LifecycleStatus::ERROR());
                $this->eventManager->dispatch(ServiceEventTopics::ERROR, $tracker->getConfig());
            }
        } else {
            $tracker->setStatus(LifecycleStatus::ACTIVE());
            $this->eventManager->dispatch(ServiceEventTopics::ACTIVATED, $tracker->getConfig());
        }
    }

    /**
     * Given a service tracker, find all other trackers dependent on this and
     * invoke reference resolving on them.
     * @param ServiceTracker $tracker
     */
    public function updateDependents(ServiceTracker $tracker) {
        Framework::debug("update dependents for {$tracker->getConfig()->getId()}");
        $this->services->getDependentsForService($tracker)
            ->traverse(function(KeyValue $kv) {
                /** @var ServiceTracker $t */
                $t = $kv->value();
                $this->resolveReferences($t);
            });
    }

    /**
     * Attempt to activate all satisfied services that aren't lazy-load.
     * @return int The number of non-active and non-satisfied services left
     */
    public function wakeUp() : int {
        $trackers = $this->services->getAllTrackers();
        $trackers->getAllByStatus(LifecycleStatus::UNSATISFIED())
            ->traverse(function(TrackerKeyValue $kv) {
                $this->resolveReferences($kv->value());
            });
        $trackers->getAllByStatus(LifecycleStatus::SATISFIED())
            ->traverse(function(TrackerKeyValue $kv) {
                $t = $kv->value();
                if ($t->getConfig()->getComponent()->isImmediate()) {
                    $this->activate($t);
                }
            });

        $activeCount = $trackers->getStatusAtleast(LifecycleStatus::SATISFIED())->count();
        return $trackers->count() - $activeCount;
    }

    public function deactivate(ServiceTracker $tracker) {
        $deactivation = $tracker->getConfig()->getComponent()->getDeactivate();
        if ($deactivation) {
            try {
                $this->eventManager->dispatch(ServiceEventTopics::DEACTIVATING, $tracker->getConfig());
                $this->unresolveReferences($tracker);
                $tracker->getIntrospector()
                    ->invokeMethod($deactivation, $tracker->getConfig()->getMetadata());
            } catch (\Exception $e) {
                Framework::debug("deactivate(): {$e->getMessage()}");
                // @todo dispatch ERROR_DEACTIVATING?
            }
        }

        // @todo catch exception
        (new ReferenceBinder($this->services, $tracker))->unbind();
        $tracker->setStatus(LifecycleStatus::DISABLED());
        // @todo invoke wakeUp() if dependent services can be fulfilled from another service
    }

    public function deactivateDependents(ServiceTracker $tracker) {
        $this->services->getDependentsForService($tracker)
            ->traverse((function(KeyValue $kv) {
                /** @var ServiceTracker $t */
                $t = $kv->value();
                $this->deactivate($t);
            }));
    }

    /**
     * Puts service back in unfilfilled state by increasing its outstanding reference counts.
     * @param ServiceTracker $tracker
     */
    public function unresolveReferences(ServiceTracker $tracker) {
        $refs = $tracker->getMarkedReferences();
        foreach ($refs as $ref) {
            $cardinality = $ref->getCardinality();
            $refQuery = $this->services->getReferenceQueryTracker($ref);
            $tracker->unmarkReference($ref);
            $tracker->getRefScoreboard()->increase($cardinality);
        }

        $tracker->setStatus(LifecycleStatus::UNSATISFIED());
    }
}
