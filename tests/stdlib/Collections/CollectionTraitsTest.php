<?php
namespace DinoTech\Phelix\tests\stdlib\Collections;

use Codeception\Test\Unit;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardMap;
use DinoTech\StdLib\KeyValue;

/**
 * Verifies general collection operations defined in traits.
 */
class CollectionTraitsTest extends Unit {
    const START = [1, 2];

    /** @var StandardList */
    private $subject;

    public function _before() {
        $this->subject = new StandardList(self::START);
    }

    public function testSlice() {
        $this->subject->arrayAddAll([3, 4]);
        $this->assertEquals(
            [1 => 2, 2 => 3],
            $this->subject->slice(1, 2)->jsonSerialize()
        );
    }

    public function testSliceBoundaries() {
        $this->assertEquals([],
            $this->subject->slice(-1, 3)->jsonSerialize());
        $this->assertEquals([1, 2],
            $this->subject->slice(0, 10)->jsonSerialize());
    }

    public function testSliceMax() {
        $this->assertEquals([1],
            $this->subject->slice(0, 10, 1)->jsonSerialize());
    }

    public function testMap() {
        $this->assertEquals(
            [2, 4],
            $this->subject->map(function(KeyValue $kv) { return $kv->value() * 2; })->jsonSerialize()
        );
    }

    public function testFilter() {
        $this->assertEquals(
            [1, 3],
            $this->subject->push(3)->filter(function(KeyValue $kv) { return $kv->value() % 2 == 1; })->jsonSerialize()
        );
    }

    public function testDiffStrict() {
        $source = new StandardMap(['a' => 1, 'b' => 2]);
        $target = ['a' => 1, 'b' => '2', 'c' => 4];
        $diffs  = $source->diff($target)->jsonSerialize();

        $this->assertEquals(2, count($diffs));
        $this->assertEquals(
            ['key' => 'b', 'sourceValue' => 2, 'targetValue' => '2'],
            $diffs['b']->jsonSerialize()
        );
        $this->assertEquals(
            ['key' => 'c', 'sourceValue' => null, 'targetValue' => 4],
            $diffs['c']->jsonSerialize()
        );
    }

    public function testUnionStrict() {
        $source = new StandardMap(['a' => 1, 'b' => 2]);
        $target = ['a' => 1, 'b' => '2', 'c' => 4];
        $union = $source->union($target)->jsonSerialize();

        $this->assertEquals(['a' => 1], $union);
    }
}
