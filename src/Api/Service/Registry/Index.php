<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Service\ServiceQuery;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardSet;
use DinoTech\StdLib\KeyValue;

class Index {
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
        $query = ServiceQuery::fromReference($reference);
        $hash = $query->getHash();

        if (!isset($this->references[$hash])) {
            $refTrack = new ReferenceQueryTracker($query);
            $this->references[$hash] = $refTrack;

            $func = function(TrackerKeyValue $kv) use ($refTrack) {
                $refTrack->addTrackerIfItMatchesQuery($kv->value());
            };

            foreach ($this->services as $list) {
                $list->traverse($func);
            }
        }

        return $this->references[$hash];
    }

    /**
     * Returns all services matching reference interface/query
     * @param ServiceReference $reference
     * @return Collection
     * @throws \DinoTech\StdLib\Collections\UnsupportedOperationException
     */
    public function getComponentsByReference(ServiceReference $reference) : Collection {
        $query = ServiceQuery::fromReference($reference);
        $refQueryTracker = ArrayUtils::get($this->references, $query->getHash());
        if ($refQueryTracker !== null) {
            return $refQueryTracker->getServiceComponents();
        }

        return new StandardList();
    }
}
