<?php
namespace DinoTech\StdLib\Collections;

use Consistence\Type\Type;
use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;
use DinoTech\StdLib\Collections\Traits\ListCollectionTrait;

class StandardList implements ListCollection {
    use CollectionTrait, ListCollectionTrait {
        ListCollectionTrait::addAll insteadof CollectionTrait;
        ListCollectionTrait::arrayAddAll insteadof CollectionTrait;
        ListCollectionTrait::map insteadof CollectionTrait;
        ListCollectionTrait::filter insteadof CollectionTrait;
    }
    use ArrayAccessTrait;
    use IteratorTrait;
    use CountableTrait;

    private $arr = [];

    public function __construct(array $arr = []) {
        $this->arr = $arr;
    }

    public function offsetGet($offset) {
        Type::checkType($offset, 'int');
        return $this->arr[$offset];
    }

    public function offsetSet($offset, $value) {
        Type::checkType($offset, 'int');
        $this->arr[$offset] = $value;
    }

    public function clear() : Collection {
        $this->arr = [];
        $this->clearIterator();
        return $this;
    }
}
