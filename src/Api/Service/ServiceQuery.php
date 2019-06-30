<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Service\Query\SimpleQueryParser;
use DinoTech\StdLib\Collections\ArrayUtils;

/**
 * ```
 * service.interface=name && metadata.type=something && component.label=Some Label
 * ```
 * @todo expand capabilities
 */
class ServiceQuery {
    /** @var array */
    private $predicates;
    /** @var string */
    private $hash;

    public function __construct(array $predicates) {
        $this->predicates = $predicates;
        $this->hash = md5(json_encode($this->predicates));
    }

    /**
     * @return string
     */
    public function getHash(): string {
        return $this->hash;
    }

    /**
     * @return array
     */
    public function getPredicates(): array {
        return $this->predicates;
    }

    /**
     * @param ServiceConfig $serviceConfig
     * @return bool
     */
    public function matchByConfig(ServiceConfig $serviceConfig) {
        $cfg = $serviceConfig->jsonSerialize();
        foreach ($this->predicates as $property => $constraint) {
            $val = ArrayUtils::getNested($cfg, $property);
            if ($constraint == null && $val != null) {
                continue;
            }

            // can't do strict compare until we can handle more expressive queries
            if ($val != $constraint) {
                return false;
            }
        }

        return true;
    }

    public static function fromReference(ServiceReference $ref) : ServiceQuery {
        $predicates = [];

        if ($ref->getQuery()) {
            $predicates = (new SimpleQueryParser($ref->getQuery()))
                ->getPredicates();
        }

        if ($ref->getInterface()) {
            $predicates['service.interface'] = $ref->getInterface();
        }

        return new static($predicates);
    }
}
