<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Service\Registry\Index;

class ServiceRegistry {
    private $services;

    private $pendingServices;

    public function __construct() {
        $this->services = new Index();
    }

    public function registerService($serviceClass, ServiceContext $context) {

    }
}
