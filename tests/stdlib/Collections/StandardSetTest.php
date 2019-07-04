<?php
namespace DinoTech\Phelix\tests\stdlib\Collections;

use Codeception\Test\Unit;
use DinoTech\StdLib\Collections\SetCollection;
use DinoTech\StdLib\Collections\StandardSet;

class StandardSetTest extends Unit {
    /** @var SetCollection */
    protected $set;

    public function _before() {
        $this->set = new StandardSet();
    }

    public function testUniqueAdds() {
        $this->set[] = 1;
        $this->set[] = '1';
        $this->set->arrayAddAll(['1', 2, 3]);

        $this->assertEquals(4, $this->set->count());
    }

    public function testUniqueFind() {
        $this->set[] = 1;
        $this->set[] = '1';
        $this->assertEquals([0], $this->set->find(1));
    }
}
