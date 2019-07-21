<?php
namespace DinoTech\StdLib\Collections\Traits;

use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\Collection;

/**
 * @property $arr array
 */
trait IteratorTrait {
    /** @var array List of iterable keys */
    private $iterKeys;
    /** @var string Current iteration key */
    private $iterCurKey;
    /** @var int Current key position */
    private $iterPos;

    public function current() {
        if ($this->iterKeys == null) {
            $this->rewind();
        }

        return ArrayUtils::get($this->arr, $this->iterCurKey);
    }

    public function next() {
        $this->iterPos++;
        $this->iterCurKey = ArrayUtils::get($this->iterKeys, $this->iterPos);
    }

    public function key() {
        return $this->iterCurKey;
    }

    public function valid() {
        return $this->iterPos < count($this->iterKeys);
    }

    public function rewind() {
        $this->iterKeys   = array_keys($this->arr);
        $this->iterPos    = 0;
        $this->iterCurKey = ArrayUtils::get($this->iterKeys, $this->iterPos);;
    }

    protected function clearIterator() : Collection {
        $this->iterKeys = null;
        $this->iterCurKey = null;
        $this->iterPos = 0;
    }
}
