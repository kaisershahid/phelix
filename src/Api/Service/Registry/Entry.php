<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Service\ServiceContext;

class Entry {
    private $rank;
    public function __construct(ServiceContext $context, $service) {
        $this->context = $context;
        $this->service = $service;
        $this->rank    = $context->getServiceMetadata()->getRank();
    }

    public function matchesQuery(array $query) {
        return $this->context->hasMetadata($query);
    }
}
