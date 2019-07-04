<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use Consistence\Type\Type;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\ListCollection;
use DinoTech\StdLib\Collections\Traits\ArrayAccessTrait;
use DinoTech\StdLib\Collections\Traits\CollectionTrait;
use DinoTech\StdLib\Collections\Traits\CountableTrait;
use DinoTech\StdLib\Collections\Traits\IteratorTrait;
use DinoTech\StdLib\Collections\Traits\ListAddAllTrait;
use DinoTech\StdLib\Collections\Traits\ListCollectionTrait;
use DinoTech\StdLib\Collections\Traits\ListOperationsTrait;
use DinoTech\StdLib\Collections\UnsupportedOperationException;

class TrackerList implements ListCollection {
    use CollectionTrait;
    use ListCollectionTrait;
    use ListAddAllTrait;
    use ListOperationsTrait;
    use IteratorTrait;
    use ArrayAccessTrait;
    use CountableTrait;

    /** @var ServiceTracker[] */
    private $arr;

    /**
     * TrackerList constructor.
     */
    public function __construct(array $trackers = []) {
        Type::checkType($trackers, ServiceTracker::class . '[]');
        $this->arr = $trackers;
        $this->preferredKeyValue = TrackerKeyValue::class;
    }

    public function clear(): Collection {
        $this->clearIterator();
        return $this;
    }

    public function getAllByStatus(LifecycleStatus $status) {
        return $this->filter(function(TrackerKeyValue $kv) use ($status) {
            return $kv->value()->getStatus() === $status;
        });
    }

    public function getStatusAtleast(LifecycleStatus $status) {
        return $this->filter(function(TrackerKeyValue $kv) use ($status) {
            return $kv->value()->getStatus()->greaterThanOrEqual($status);
        });
    }
}
