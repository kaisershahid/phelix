<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardSet;
use DinoTech\StdLib\KeyValue;

class Index implements \JsonSerializable {
    /** @var StandardList[] of trackers */
    private $services = [];
    /** @var ReferenceQueryTracker[] */
    private $references = [];

    public function add(ServiceTracker $tracker) : Index {
        $config = $tracker->getConfig();
        $interface = $config->getInterface() ?: '';
        if (!isset($this->services[$interface])) {
            $this->services[$interface] = (new StandardList())
                ->setKeyValueClass(TrackerKeyValue::class);
        }

        $this->services[$interface]->push($tracker);
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

    /**
     * Adds a service reference to cache, and adds existing services that match
     * the query.
     * @param ServiceReference $reference
     */
    public function addReference(ServiceReference $reference) {
        $refTrack = $this->getReferenceQueryTracker($reference);
        if ($refTrack === null) {
            $refTrack = $this->makeReferenceQueryTracker($reference);
            $func = function(TrackerKeyValue $kv) use ($refTrack) {
                $refTrack->addTrackerIfItMatchesQuery($kv->value());
            };

            foreach ($this->services as $list) {
                $list->traverse($func);
            }
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
        $query = ServiceQuery::fromReference($reference);
        return ArrayUtils::get($this->references, $query->getHash());
    }

    protected function makeReferenceQueryTracker(ServiceReference $reference) : ReferenceQueryTracker {
        $query = ServiceQuery::fromReference($reference);
        $refTrack = new ReferenceQueryTracker($query);
        $this->references[$query->getHash()] = $refTrack;
        return $refTrack;
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

    public function getAllTrackers() {
        $trackers = new StandardList();
        foreach ($this->services as $list) {
            $trackers->addAll($list);
        }

        return $trackers;
    }
}
