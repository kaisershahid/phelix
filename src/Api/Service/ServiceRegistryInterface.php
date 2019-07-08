<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\StdLib\Collections\Collection;

interface ServiceRegistryInterface {
    public function getService($interface);

    public function getServices($interface) : Collection;

    public function getServicesByQuery(ServiceQuery $query) : Collection;

    public function getServicesByReference(ServiceReference $reference) : Collection;
}
