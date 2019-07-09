<?php

namespace DinoTech\Phelix\Api\Service\Lifecycle;

use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\Registry\ServiceTracker;
use DinoTech\Phelix\Api\Service\ServiceEventTopics;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\KeyValue;

class Deactivator {
    /** @var EventManagerInterface */
    private $eventManager;
    /** @var Index */
    private $services;

    public function __construct(Index $services, EventManagerInterface $eventManager) {
        $this->services = $services;
        $this->eventManager = $eventManager;
    }

    public function deactivate(ServiceTracker $tracker) {
        $deactivation = $tracker->getConfig()->getComponent()->getDeactivate();
        if ($deactivation) {
            try {
                $this->eventManager->dispatch(ServiceEventTopics::DEACTIVATING, $tracker->getConfig());
                $tracker->getIntrospector()
                    ->invokeMethod($deactivation, $tracker->getConfig()->getMetadata());
            } catch (\Exception $e) {
                Framework::debug("deactivate(): {$e->getMessage()}");
                // @todo dispatch ERROR_DEACTIVATING?
            }
        }

        $this->unresolveReferences($tracker);
        // @todo invoke wakeUp() in case dependent services can be fulfilled by another service
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
     * Unbinds references and unmarks them so that the next time this is activated,
     * references will be re-checked. Sets service as unsatisfied.
     * @param ServiceTracker $tracker
     */
    public function unresolveReferences(ServiceTracker $tracker) {
        // @todo catch exception
        (new ReferenceBinder($this->services, $tracker))->unbind();

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
