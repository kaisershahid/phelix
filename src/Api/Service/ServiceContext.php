<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Service\Metadata\ComponentMetadata;
use DinoTech\Phelix\Api\Service\Metadata\PropertiesMetadata;
use DinoTech\Phelix\Api\Service\Metadata\ReferencesMetadata;
use DinoTech\Phelix\Api\Service\Metadata\ServiceMetadata;
use DinoTech\StdLib\GenericMap;

/**
 * Encapsulates all the service configuration data.
 */
class ServiceContext {
    /** @var GenericMap */
    private $metadata;

    public function getServiceMetadata() : ServiceMetadata {

    }

    public function getComponentMetadata() : ComponentMetadata {

    }

    public function getPropertyMetadata() : PropertiesMetadata {

    }

    public function getReferenceMetadata() : ReferencesMetadata {

    }

    public function hasMetadata(array $metadata) {
        return $this->metadata->hasSubset($metadata);
    }
}
