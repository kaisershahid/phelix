<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\MapCollection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;
use DinoTech\StdLib\Collections\Traits\MapCollectionTrait;
use DinoTech\StdLib\Collections\UnsupportedOperationException;

class ServiceConfig implements \JsonSerializable {
    const KEY_INTERFACE = 'interface';
    const KEY_CLASS = 'class';
    const KEY_RANK = 'rank';
    const KEY_ID = 'id';
    const KEY_COMPONENT = 'component';
    const KEY_METADATA = 'metadata';
    const KEY_REFERENCES = 'references';

    /** @var array */
    private $conf;
    /** @var string */
    private $id;
    /** @var string */
    private $interface;
    /** @var string */
    private $class;
    /** @int rank */
    private $rank;
    /** @var ServiceComponent */
    private $component;
    /** @var ServiceReference[] */
    private $references;
    /** @var ServiceMetadata */
    private $metadata;

    public function __construct(array $arr = []) {
        $this->conf = $arr;
        $this->interface = ArrayUtils::get($arr, self::KEY_INTERFACE);
        $this->class = ArrayUtils::get($arr, self::KEY_CLASS);
        $this->id = ArrayUtils::get($arr, self::KEY_ID, $this->class);
        $this->rank = ArrayUtils::get($arr, self::KEY_RANK, 0);
        $this->component = new ServiceComponent(ArrayUtils::get($arr, self::KEY_COMPONENT, []));
        $this->metadata = new ServiceMetadata(ArrayUtils::get($arr, self::KEY_METADATA, []));
        $this->references = array_map(function(array $ref) {
            return new ServiceReference($ref);
        }, ArrayUtils::get($arr, self::KEY_REFERENCES, []));
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getInterface(): string {
        return $this->interface ?: $this->class;
    }

    /**
     * @return string
     */
    public function getClass(): string {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getRank() {
        return $this->rank;
    }

    /**
     * @return ServiceComponent
     */
    public function getComponent(): ServiceComponent {
        return $this->component;
    }

    /**
     * @return ServiceReference[]|ListCollection
     */
    public function getReferences(): ListCollection {
        return new StandardList($this->references);
    }

    /**
     * @return ServiceMetadata
     */
    public function getMetadata(): ServiceMetadata {
        return $this->metadata;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'interface' => $this->interface,
            'class' => $this->class,
            'rank' => $this->rank,
            'component' => $this->component,
            // @todo properties
            'metadata' => $this->metadata->jsonSerialize(),
            'references' => array_map(function(ServiceReference $ref) {
                return $ref->jsonSerialize();
            }, $this->references)
        ];
    }
}
