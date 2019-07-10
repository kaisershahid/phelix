<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\MapCollection;
use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;
use DinoTech\StdLib\Collections\Traits\MapAddAllTrait;
use DinoTech\StdLib\Collections\Traits\MapCollectionTrait;
use DinoTech\StdLib\Collections\Traits\MapOperationsTrait;
use DinoTech\StdLib\Collections\UnsupportedOperationException;

/**
 * Holds a generic set of key-value pair.
 * @todo make immutable
 */
class ServiceProperties implements MapCollection {
    use CollectionTrait;
    use MapCollectionTrait;
    use MapOperationsTrait;
    use IteratorTrait;
    use ArrayAccessTrait;
    use CountableTrait;

    private $arr;

    public function __construct(array $arr = []) {
        $this->arr = $arr;
    }

    public function clear() : Collection {
        return $this;
    }

    function addAll(Collection $arr): Collection {
        return $this->arrayAddAll($arr->jsonSerialize());
    }

    public function arrayAddAll(array $arr): Collection {
        $props = array_replace($this->arr, $arr);
        return new self($props);
    }
}
