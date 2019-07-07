<?php
namespace DinoTech\Phelix\Api\Bundle;

class BundleTracker {
    /** @var BundleManifest */
    private $manifest;
    /** @var BundleStatus */
    private $status;
    /** @var BundleActivatorInterface */
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
     * @return BundleTracker
     */
    public function setManifest(BundleManifest $manifest): BundleTracker {
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
     * @return BundleTracker
     */
    public function setStatus(BundleStatus $status): BundleTracker {
        $this->status = $status;
        return $this;
    }

    /**
     * @return BundleActivatorInterface
     */
    public function getActivator(): ?BundleActivatorInterface {
        return $this->activator;
    }

    /**
     * @param BundleActivatorInterface $activator
     * @return BundleTracker
     */
    public function setActivator(BundleActivatorInterface $activator): BundleTracker {
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
     * @return BundleTracker
     */
    public function setLifecycleException(\Exception $lifecycleException): BundleTracker {
        $this->lifecycleException = $lifecycleException;
        return $this;
    }
}
