<?php
namespace DinoTech\Phelix\tests\stdlib\Collections;

use Codeception\Test\Unit;
use DinoTech\StdLib\Collections\StandardMap;

class StandardMapTest extends Unit {
    const START = ['a' => 1, 'b' => 2];

    /** @var StandardMap */
    private $subject;

    public function _before() {
        $this->subject = new StandardMap(self::START);
    }

    public function testPluckKeys() {
        $newMap = $this->subject->pluckKeys('a');
        $this->assertEquals(['a' => 1], $newMap->jsonSerialize());
        $this->assertEquals(['b' => 2], $this->subject->jsonSerialize());
    }
}
