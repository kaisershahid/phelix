<?php

namespace DinoTech\Phelix\Api\Service\Lifecycle;

use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\Registry\ServiceTracker;
use DinoTech\Phelix\Api\Service\Registry\TrackerKeyValue;
use DinoTech\Phelix\Api\Service\ServiceEventTopics;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\KeyValue;

class Activator {
    /** @var EventManagerInterface */
    private $eventManager;
    /** @var Index */
    private $services;

    public function __construct(Index $services, EventManagerInterface $eventManager) {
        $this->services = $services;
        $this->eventManager = $eventManager;
    }

    public function activate(ServiceTracker $tracker) {
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
        if ($tracker->getStatus() === LifecycleStatus::ACTIVE()) {
            return;
        }

        if ($tracker->getRefScoreboard()->getTotalScore() == 0) {
            $tracker->setStatus(LifecycleStatus::SATISFIED());
            if ($tracker->getConfig()->getComponent()->isImmediate() || $forceActivation) {
                $this->invokeActivate($tracker);
            }
        } else {
            Framework::debug("unsatified: {$tracker->getConfig()->getId()}");
        }
    }

    public function invokeActivate(ServiceTracker $tracker) {
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
}
