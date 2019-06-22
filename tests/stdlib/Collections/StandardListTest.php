<?php
namespace DinoTech\Phelix\tests\unit\StdLib\Collections;

use Codeception\Test\Unit;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\KeyValue;

/**
 * @todo need more tests here
 * @todo test for map
 */
class StandardListTest extends Unit {
    const START = [1, 2];

    /** @var StandardList */
    private $subject;

    public function _before() {
        $this->subject = new StandardList(self::START);
    }

    public function testArrayAddAll() {
        $this->subject->arrayAddAll(['a' => 3, 'b' => 4]);
        $this->assertEquals(
            [1, 2, 3, 4],
            $this->subject->jsonSerialize()
        );
    }

    public function testPushPop() {
        $this->subject->push(99);
        $this->assertEquals(
            [1, 2, 99],
            $this->subject->jsonSerialize()
        );

        $pop = $this->subject->pop();
        $this->assertEquals(99, $pop);
        $this->assertEquals(
            self::START,
            $this->subject->jsonSerialize()
        );
    }

    public function testUnshiftShift() {
        $this->subject->unshift(99);
        $this->assertEquals(
            [99, 1, 2],
            $this->subject->jsonSerialize()
        );

        $pop = $this->subject->shift();
        $this->assertEquals(99, $pop);
        $this->assertEquals(
            self::START,
            $this->subject->jsonSerialize()
        );
    }
}
