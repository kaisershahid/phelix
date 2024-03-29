<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Service\Lifecycle\Introspector;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\KeyValue;

class ServiceTracker implements \JsonSerializable {
    /** @var object */
    private $component;
    /** @var ServiceConfig */
    private $config;
    /** @var BundleManifest */
    private $manifest;
    /** @var Scoreboard */
    private $refScoreboard;
    /** @var ServiceReference[]ListCollection */
    private $refs;
    /** @var array */
    private $markedRefs = [];
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
    public function getManifest(): BundleManifest {
        return $this->manifest;
    }

    /**
     * @return Scoreboard
     */
    public function getRefScoreboard(): Scoreboard {
        return $this->refScoreboard;
    }

    /**
     * Returns all unmarked references.
     * @return ServiceReference[]|ListCollection
     */
    public function getUnmarkedReferences() : ListCollection {
        return $this->refs->filter(function(KeyValue $kv) {
            return !isset($this->markedRefs[$kv->value()->getRefNum()]);
        });
    }

    /**
     * Returns all marked references.
     * @return ServiceReference[]|ListCollection
     */
    public function getMarkedReferences() : ListCollection {
        return $this->refs->filter(function(KeyValue $kv) {
            return isset($this->markedRefs[$kv->value()->getRefNum()]);
        });
    }

    public function markReference(ServiceReference $ref) : ServiceTracker {
        $this->markedRefs[$ref->getRefNum()] = true;
        return $this;
    }

    public function unmarkReference(ServiceReference $ref) : ServiceTracker {
        unset($this->markedRefs[$ref->getRefNum()]);
        return $this;
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
