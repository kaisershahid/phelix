<?php
namespace DinoTech\StdLib\Collections;

use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;

class StandardCollection implements Collection {
    use CollectionTrait;
    use ArrayAccessTrait;
    use IteratorTrait;
    use CountableTrait;

    private $arr;

    public function __construct(array $arr = []) {
        $this->arr = $arr;
    }

    public function clear() : Collection {
        $this->arr = [];
        $this->clearIterator();
        return $this;
    }
}