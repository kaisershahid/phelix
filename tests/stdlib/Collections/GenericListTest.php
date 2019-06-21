<?php
namespace DinoTech\Phelix\tests\unit\StdLib\Collections;

use Codeception\Test\Unit;
use DinoTech\StdLib\Collections\GenericList;
use DinoTech\StdLib\KeyValue;

/**
 * @todo need more tests here
 * @todo test for map
 */
class GenericListTest extends Unit {
    const START = [1, 2];

    /** @var GenericList */
    private $subject;

    public function _before() {
        $this->subject = new GenericList(self::START);
    }

    public function testArrayAddAll() {
        $this->subject->arrayAddAll(['a' => 3, 'b' => 4]);
        $this->assertEquals(
            [1, 2, 3, 4],
            $this->subject->jsonSerialize()
        );
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
}
