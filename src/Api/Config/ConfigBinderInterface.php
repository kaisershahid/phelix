<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\Phelix\Env;

/**
 * A configuration binding is just a set of config properties for a service. The
 * binder maps a service to a configuration through some medium (e.g. filesys,
 * memcached, etc.).
 */
interface ConfigBinderInterface {
    public function getConfigBinding(string $serviceId) : ServiceProperties;
}
