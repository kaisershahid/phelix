<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Service\Lifecycle\Introspector;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\StandardList;
use PharIo\Manifest\Manifest;

class ServiceTracker implements \JsonSerializable {
    /** @var object */
    private $component;
    /** @var ServiceConfig */
    private $config;
    /** @var Manifest */
    private $manifest;
    /** @var Scoreboard */
    private $refScoreboard;
    /** @var ServiceReference[]ListCollection */
    private $refs;
    /** @var LifecycleStatus */
    private $status;
    /** @var Introspector */
    private $introspector;

    public function __construct($component, ServiceConfig $config, BundleManifest $manifest) {
        $this->component = $component;
        $this->config = $config;
        $this->manifest = $manifest;
        $this->refScoreboard = Scoreboard::makeForCardinality();
        $this->refs = new StandardList($config->getReferences()->values());
        $this->status = $config->getComponent()->isImmediate() ?
            LifecycleStatus::UNSATISFIED() : LifecycleStatus::DISABLED();
        $this->initScoreboard();
    }

    protected function initScoreboard() {
         foreach ($this->refs as $ref) {
            $this->refScoreboard->increase($ref->getCardinality());
        }
    }

    /**
     * @return object
     */
    public function getComponent() {
        return $this->component;
    }

    /**
     * @return ServiceConfig
     */
    public function getConfig(): ServiceConfig {
        return $this->config;
    }

    /**
     * @return Manifest
     */
    public function getManifest(): Manifest {
        return $this->manifest;
    }

    /**
     * @return Scoreboard
     */
    public function getRefScoreboard(): Scoreboard {
        return $this->refScoreboard;
    }

    /**
     * @return ServiceReference[]|ListCollection
     */
    public function getRefs(): ListCollection {
        return $this->refs;
    }

    /**
     * @return LifecycleStatus
     */
    public function getStatus(): LifecycleStatus {
        return $this->status;
    }

    /**
     * @param LifecycleStatus $status
     * @return ServiceTracker
     */
    public function setStatus(LifecycleStatus $status): ServiceTracker {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Introspector
     */
    public function getIntrospector(): Introspector {
        return $this->introspector;
    }

    /**
     * @param Introspector $introspector
     * @return ServiceTracker
     */
    public function setIntrospector(Introspector $introspector): ServiceTracker {
        $this->introspector = $introspector;
        return $this;
    }

    public function jsonSerialize() {
        $data = [
            'status' => $this->status->name(),
            'refScoreboard' => $this->refScoreboard->jsonSerialize(),
            'manifest' => $this->manifest->jsonSerialize(),
            'serviceConfig' => $this->config->jsonSerialize(),
        ];

        return $data;
    }
}
