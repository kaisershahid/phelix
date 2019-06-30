<?php
namespace DinoTech\Phelix\Api\Service\Lifecycle;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\Phelix\Api\Service\ReferenceCardinality;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\Registry\Scoreboard;
use DinoTech\Phelix\Api\Service\Registry\ServiceTracker;

class DefaultManager {
    /** @var Index */
    private $services;
    /** @var ServiceTracker[] */
    private $servicesInWaiting = [];
    /** @var Scoreboard */
    private $depScoreboard;

    public function __construct(Index $services) {
        $this->services = $services;
        $this->depScoreboard = Scoreboard::makeFromCollection(ReferenceCardinality::values());
    }

    public function startService(ServiceConfig $config, BundleManifest $manifest) {
        if (!$config->getComponent()->isEnabled()) {
            return;
        }

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
            $refQuery = $this->services->getReferenceQueryTracker($ref);

            if ($cardinality->isMandatory()) {
                if ($refQuery->hasOneSatisfied()) {
                    $tracker->getRefScoreboard()->decrease($cardinality);
                }
            } else {
                $tracker->getRefScoreboard()->decrease($cardinality);
            }
        }

        $this->activate($ref);
    }

    public function activateIfReferencesSatisfied(ServiceTracker $tracker) {
        if ($tracker->getRefScoreboard()->getTotalScore() == 0) {
            $tracker->setStatus(LifecycleStatus::SATISFIED());
            if ($tracker->getConfig()->getComponent()->isImmediate()) {
                $this->activate($tracker);
            }
        }
    }

    public function activate(ServiceTracker $tracker) {
        // @todo resolve configuration & merge with metadata
        // @todo activate every satisfied ref
        $activation = $tracker->getConfig()->getComponent()->getActivate();
        if ($activation) {
            try {
                $tracker->getIntrospector()->invokeMethod($activation, $tracker->getConfig()->getMetadata());
                $tracker->setStatus(LifecycleStatus::ACTIVE());
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
}
