<?php
namespace DinoTech\Phelix\Api\Bundle;

class BundleRegistry {
    public function registerBundle(BundleManifest $manifest) {
        // @todo check dependencies -- if not all found, put in wait queue
        // @todo if group id/bundle id exist...?
        // @todo after activated, check if this resolves other deps in queue
    }
}
