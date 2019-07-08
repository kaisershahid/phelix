<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\SetCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardSet;
use DinoTech\StdLib\KeyValue;

/**
 * Contains the master list of all framework services.
 */
class Index implements \JsonSerializable {
    /** @var TrackerList[] of trackers */
    private $services = [];
    /** @var ReferenceQueryTracker[] */
    private $references = [];

    public function add(ServiceTracker $tracker) : Index {
        $config = $tracker->getConfig();
        $interface = $config->getInterface() ?: '';
        if (!isset($this->services[$interface])) {
            $this->services[$interface] = new TrackerList();
        }

        $this->services[$interface]->push($tracker);
        $this->checkAndAddToReferences($tracker);
        return $this;
    }

    public function get(string $interface) {
        if (!isset($this->services[$interface])) {
            return null;
        }

        return $this->services[$interface][0];
    }

    public function getAll(string $interface) {
        if (!isset($this->services[$interface])) {
            return [];
        }

        return $this->services[$interface]->values();
    }

    public function checkAndAddToReferences(ServiceTracker $tracker) {
        foreach ($this->references as $refTracker) {
            $refTracker->addTrackerIfItMatchesQuery($tracker);
        }
    }

    public function search(ServiceQuery $query) : ReferenceQueryTracker {
        $refTrack = new ReferenceQueryTracker($query);
        $this->fillReferenceTracker($refTrack);
        return $refTrack;
    }

    public function fillReferenceTracker(ReferenceQueryTracker $refTrack) {
        $func = function(TrackerKeyValue $kv) use ($refTrack) {
            $refTrack->addTrackerIfItMatchesQuery($kv->value());
        };

        foreach ($this->services as $list) {
            Framework::debug("fillReferenceTracker ({$refTrack->getHash()}): back-adding");
            $list->traverse($func);
        }
    }

    /**
     * Adds a service reference to cache, and adds existing services that match
     * the query.
     * @param ServiceReference $reference
     */
    public function addReference(ServiceReference $reference) {
        $refTrack = $this->getReferenceQueryTracker($reference);
        if ($refTrack === null) {
            $refTrack = $this->makeReferenceQueryTracker($reference);
            $this->fillReferenceTracker($refTrack);
        }

        return $refTrack;
    }

    /**
     * Returns all services matching reference interface/query
     * @param ServiceReference $reference
     * @return Collection
     * @throws \DinoTech\StdLib\Collections\UnsupportedOperationException
     */
    public function getComponentsByReference(ServiceReference $reference) : Collection {
        $refQueryTracker = $this->getReferenceQueryTracker($reference);
        if ($refQueryTracker !== null) {
            return $refQueryTracker->getServiceComponents();
        }

        return new StandardList();
    }

    public function getReferenceQueryTracker(ServiceReference $reference) : ?ReferenceQueryTracker {
        return $this->getQueryTracker(ServiceQuery::fromReference($reference));
    }

    public function getQueryTracker(ServiceQuery $query) : ?ReferenceQueryTracker {
        return ArrayUtils::get($this->references, $query->getHash());
    }

    protected function makeReferenceQueryTracker(ServiceReference $reference) : ReferenceQueryTracker {
        $query = ServiceQuery::fromReference($reference);
        $refTrack = new ReferenceQueryTracker($query);
        $this->references[$query->getHash()] = $refTrack;
        return $refTrack;
    }

    /**
     * Returns all dependent services for a given service.
     * @param ServiceTracker $tracker
     * @param ServiceTracker[]|SetCollection
     */
    public function getDependentsForService(ServiceTracker $tracker) {
        /** @var ServiceTracker[]|SetCollection $dependents */
        $dependents = new StandardSet();
        foreach ($this->references as $reference) {
            if ($reference->hasTracker($tracker)) {
                $dependents->addAll($reference->getDependents());
            }
        }


        return $dependents;
    }

    public function jsonSerialize() {
        $data = [];
        $data['services'] = [];
        foreach ($this->services as $interface => $servicesList) {
            $data['services'][$interface] = [];
            $servicesList->traverse(function(TrackerKeyValue $kv) use (&$data, $interface) {
                $data['services'][$interface][$kv->key()] = $kv->value()->jsonSerialize();
            });
        }

        return $data;
    }

    public function jsonSerializeExtended() {
        $data = $this->jsonSerialize();
        $data['refs'] = [];

        return $data;
    }

    public function getAllTrackers() : TrackerList {
        $trackers = new TrackerList();
        foreach ($this->services as $list) {
            $trackers->addAll($list);
        }

        return $trackers;
    }
}
