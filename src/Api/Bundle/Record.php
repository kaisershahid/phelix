<?php
namespace DinoTech\Phelix\Api\Bundle;

class Record {
    /** @var BundleManifest */
    private $manifest;
    /** @var BundleStatus */
    private $status;
    /** @var BundleActivator */
    private $activator;
    /** @var \Exception */
    private $lifecycleException;

    /**
     * @return BundleManifest
     */
    public function getManifest(): BundleManifest {
        return $this->manifest;
    }

    /**
     * @param BundleManifest $manifest
     * @return Record
     */
    public function setManifest(BundleManifest $manifest): Record {
        $this->manifest = $manifest;
        return $this;
    }

    /**
     * @return BundleStatus
     */
    public function getStatus(): BundleStatus {
        return $this->status;
    }

    /**
     * @param BundleStatus $status
     * @return Record
     */
    public function setStatus(BundleStatus $status): Record {
        $this->status = $status;
        return $this;
    }

    /**
     * @return BundleActivator
     */
    public function getActivator(): BundleActivator {
        return $this->activator;
    }

    /**
     * @param BundleActivator $activator
     * @return Record
     */
    public function setActivator(BundleActivator $activator): Record {
        $this->activator = $activator;
        return $this;
    }

    /**
     * @return \Exception
     */
    public function getLifecycleException(): \Exception {
        return $this->lifecycleException;
    }

    /**
     * @param \Exception $lifecycleException
     * @return Record
     */
    public function setLifecycleException(\Exception $lifecycleException): Record {
        $this->lifecycleException = $lifecycleException;
        return $this;
    }
}
