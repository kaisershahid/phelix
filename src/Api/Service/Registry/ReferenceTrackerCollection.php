<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\StdLib\Collections\StandardList;

class ReferenceTrackerCollection extends StandardList {
    protected $serviceRef;
    protected $cardinality;

    protected function setServiceReference(ServiceReference $config) : ReferenceTrackerCollection {
        $this->serviceRef = $config;
        $this->cardinality = $config->getCardinality();
    }

    public function getServiceReference() : ServiceReference {
        return $this->serviceRef;
    }

    public function isSatisfied() : bool {
        // @todo return based on cardinality + list size
        return false;
    }

    public static function fromConfig(ServiceReference $config) {
        return (new self())->setServiceReference($config);
    }
}
