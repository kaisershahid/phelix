<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardSet;
use DinoTech\StdLib\KeyValue;

/**
 * The reference query tracker is bound to a query (more specifically, its hash
 * value) and is used to track both services that fulfill the query and services
 * that are dependent on it.
 */
class ReferenceQueryTracker {
    /** @var ServiceQuery */
    private $query;
    /** @var string */
    private $queryHash;
    /** @var ServiceTracker[]|ListCollection */
    private $serviceTrackers;
    /** @var ServiceTracker[]|ListCollection */
    private $dependentServiceTrackers;

    public function __construct(ServiceQuery $query) {
        $this->query = $query;
        $this->queryHash = $query->getHash();
        $this->serviceTrackers = (new StandardList())->setKeyValueClass(TrackerKeyValue::class);
        $this->dependentServiceTrackers = new StandardSet();
    }

    public function addTrackerIfItMatchesQuery(ServiceTracker $tracker) {
        if ($this->query->matchByConfig($tracker->getConfig())) {
            $this->addTracker($tracker);
            return true;
        }

        return false;
    }

    public function addTracker(ServiceTracker $tracker) : ReferenceQueryTracker {
        $this->serviceTrackers->push($tracker);
        return $this;
    }

    public function getServiceComponents() : ListCollection {
        return $this->serviceTrackers->map(function(TrackerKeyValue $kv) {
            return $kv->value()->getComponent();
        });
    }

    public function removeTracker(ServiceTracker $tracker) : ?ServiceTracker {
        return $this->serviceTrackers->removeFirst($tracker);
    }

    /**
     * Registers a tracker that's dependent on 1+ trackers fulfilled by the query.
     * @param ServiceTracker $tracker
     * @return ReferenceQueryTracker
     */
    public function addDependent(ServiceTracker $tracker) : ReferenceQueryTracker {
        $this->dependentServiceTrackers->push($tracker);
        return $this;
    }

    public function removeDependent(ServiceTracker $tracker) : ?ServiceTracker {
        return $this->dependentServiceTrackers->removeFirst($tracker);
    }
}
