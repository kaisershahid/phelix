<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\ArrayUtils;

class ServiceRegistryConfig {
    /** @var ServiceConfig[] */
    private $serviceConfigs;

    public function __construct(array $arr) {
        $this->serviceConfigs = array_map(function(array $svc) {
            return new ServiceConfig($svc);
        }, ArrayUtils::get($arr, 'services', []));
    }

    public function getServiceConfigs() : array {
        return $this->serviceConfigs;
    }
}
