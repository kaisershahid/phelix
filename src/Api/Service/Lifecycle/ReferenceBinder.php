<?php
namespace DinoTech\Phelix\Api\Service\Lifecycle;

use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\Registry\ServiceTracker;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\KeyValue;

/**
 * Wraps binding/unbinding references to a service component.
 */
class ReferenceBinder {
    /** @var Index */
    private $services;
    /** @var ServiceTracker */
    private $tracker;

    public function __construct(Index $services, ServiceTracker $tracker) {
        $this->services= $services;
        $this->tracker = $tracker;
    }

    public function bind() {
        $references = $this->tracker->getConfig()->getReferences();
        foreach ($references as $reference) {
            $svcTrackers = $this->services->getReferenceQueryTracker($reference)
                ->getTrackersByRank()
                ->map(function(KeyValue $kv) {
                    /** @var ServiceTracker $t */
                    $t = $kv->value();
                    return $t->getComponent();
                });
            $cardinality = $reference->getCardinality();
            if ($cardinality->isOne()) {
                $this->doBind($svcTrackers[0], $reference);
            } else {
                $this->doBind($svcTrackers, $reference);
            }
        }
    }

    protected function doBind($component, ServiceReference $reference) {
        $bind = $reference->getBind();
        $target = $reference->getTarget();
        if ($bind) {
            $this->tracker->getIntrospector()
                ->invokeMethod($bind, $component);
        } else if ($target) {
            $this->tracker->getIntrospector()
                ->setProperty($target, $component);
        } else {
            Framework::debug("**WARNING** could not bind!!!");
            // @todo throw exception?
        }
    }

    public function unbind() {
        $references = $this->tracker->getConfig()->getReferences();
        foreach ($references as $reference) {
            $this->doUnbind($reference);
        }
    }

    protected function doUnbind(ServiceReference $reference) {
        $unbind = $reference->getUnbind();
        $target = $reference->getTarget();
        if ($unbind) {
            $this->tracker->getIntrospector()
                ->invokeMethod($unbind);
        } else if ($target) {
            $this->tracker->getIntrospector()
                ->setProperty($target, null);
        } else {
            Framework::debug("**WARNING** could not unbind!!");
            // @todo throw exception?
        }
    }
}
