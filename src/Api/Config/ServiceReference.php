<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\ArrayUtils as Arr;
use DinoTech\Phelix\Api\Service\ReferenceCardinality;

class ServiceReference implements \JsonSerializable {
    const KEY_INTERFACE = 'interface';
    const KEY_QUERY = 'query';
    const KEY_CARDINALITY = 'cardinality';
    const KEY_BIND = 'bind';
    const KEY_UNBIND = 'unbind';
    const KEY_TARGET = 'target';

    /** @var string */
    private $interface;
    /** @var string */
    private $query;
    /** @var ReferenceCardinality */
    private $cardinality;
    /** @var string */
    private $bind;
    /** @var string */
    private $unbind;
    /** @var string */
    private $target;

    public function __construct(array $arr) {
        $this->interface = Arr::get($arr, self::KEY_INTERFACE);
        $this->query = Arr::get($arr, self::KEY_QUERY);
        $this->cardinality = ReferenceCardinality::fromName(Arr::get($arr, self::KEY_CARDINALITY, 'one'));
        $this->bind = Arr::get($arr, self::KEY_BIND);
        $this->unbind = Arr::get($arr, self::KEY_UNBIND);
        $this->target = Arr::get($arr, self::KEY_TARGET);
    }

    /**
     * @return string
     */
    public function getInterface(): string {
        return $this->interface;
    }

    /**
     * @return string
     */
    public function getQuery(): string {
        return $this->query;
    }

    /**
     * @return ReferenceCardinality
     */
    public function getCardinality(): ReferenceCardinality {
        return $this->cardinality;
    }

    /**
     * @return string
     */
    public function getBind(): string {
        return $this->bind;
    }

    /**
     * @return string
     */
    public function getUnbind(): string {
        return $this->unbind;
    }

    /**
     * @return string
     */
    public function getTarget(): string {
        return $this->target;
    }

    public function jsonSerialize() {
        return [
            self::KEY_INTERFACE => $this->interface,
            self::KEY_QUERY => $this->query,
            self::KEY_CARDINALITY => $this->cardinality->name(),
            self::KEY_BIND => $this->bind,
            self::KEY_UNBIND => $this->unbind,
            self::KEY_TARGET => $this->target
        ];
    }
}
