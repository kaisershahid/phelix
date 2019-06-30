<?php
namespace DinoTech\Phelix\tests\unit\Service\Registry;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\ReferenceCardinality;
use DinoTech\Phelix\Api\Service\Registry\Scoreboard;

class ScoreboardTest extends Unit {
    public function testManipulationByEnum() {
        $one = ReferenceCardinality::ONE();
        $oneOpt = ReferenceCardinality::ONE_OPTIONAL();
        $scoreboard = Scoreboard::makeFromCollection(ReferenceCardinality::values());
        $scoreboard->increase($one);
        $scoreboard->increase($oneOpt, 2);

        $this->assertEquals(3, $scoreboard->getTotalScore());
        $this->assertEquals(1, $scoreboard->getScore($one));
    }
}
