<?php
namespace DinoTech\Phelix\Api\Service\Lifecycle;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\Registry\Scoreboard;
use DinoTech\Phelix\Api\Service\Registry\ServiceTracker;
use DinoTech\Phelix\Api\Service\Registry\TrackerKeyValue;
use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\KeyValue;

/**
 * The bootstrap lifecycle manager that is fully dynamic and automated in service
 * activation and deactivation (e.g. not dependent on any caching or service
 * ordering).
 *
 * @todo make an interface LifecycleManager
 * @todo track service activation order to provide insights into optimizing for other managers
 */
class DefaultManager {
    /** @var Index */
    private $services;
    /** @var ServiceTracker[] */
    private $servicesInWaiting = [];
    /** @var Scoreboard */
    private $depScoreboard;

    public function __construct(Index $services) {
        $this->services = $services;
        $this->depScoreboard = Scoreboard::makeForCardinality();
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
        $refs = $tracker->getRefs();
        if ($refs->count() > 0) {
            $this->resolveReferences($tracker);
        } else {
            $this->activateIfReferencesSatisfied($tracker);
        }
    }

    public function resolveReferences(ServiceTracker $tracker) {
        $refs = $tracker->getRefs();
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

    public function activateIfReferencesSatisfied(ServiceTracker $tracker) {
        if ($tracker->getRefScoreboard()->getTotalScore() <= 0) {
            $tracker->setStatus(LifecycleStatus::SATISFIED());
            if ($tracker->getConfig()->getComponent()->isImmediate()) {
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

        // @todo resolve configuration & merge with metadata
        $activation = $tracker->getConfig()->getComponent()->getActivate();
        if ($activation) {
            try {
                // @todo send ServiceProperties instead of metadata
                // @todo bind references
                Framework::debug("activating {$tracker->getConfig()->getId()}");
                $tracker->getIntrospector()
                    ->invokeMethod($activation, $tracker->getConfig()->getMetadata());
                $tracker->setStatus(LifecycleStatus::ACTIVE());
                $this->updateDependents($tracker);
                // @todo trigger service activation event
            } catch (\Exception $e) {
                codecept_debug($e->getMessage());
                // @todo capture exception message in tracker & log
                $tracker->setStatus(LifecycleStatus::ERROR());
            }
        } else {
            $tracker->setStatus(LifecycleStatus::ACTIVE());
            // @todo trigger service activation event
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

    public function getService(ServiceQuery $query) {
        // @todo fetch trackers
        // @todo activate satisfied services
        // @todo return components
    }
}
