<?php
namespace DinoTech\Phelix\Api\Bundle;

use DinoTech\Phelix\Api\Event\EventInterface;
use DinoTech\Phelix\Api\Service\ServiceInterface;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;

/**
 * If a `BundleInterface` is defined for a bundle, it is initialized first
 * before any other service, and
 */
interface BundleInterface {
    public function startBundle(Framework $framework, ServiceRegistry $serviceRegistry);

    public function handleServiceEvent(EventInterface $event, ServiceInterface $service);

    public function stopBundle(ServiceRegistry $serviceRegistry);
}
