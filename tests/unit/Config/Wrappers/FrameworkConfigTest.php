<?php
namespace DinoTech\Phelix\tests\unit\Config\Wrappers;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Config\Wrappers\FrameworkConfig;

class FrameworkConfigTest extends Unit {

    public function testBasics() {
        $subject = FrameworkConfig::makeWithDefaults(['properties' => ['a' => 1, 'b' => 2]]);
        $this->assertEquals(['a' => 1, 'b' => 2],
            $subject->getProperties()->jsonSerialize());
    }
}
