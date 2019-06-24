<?php
namespace DinoTech\Phelix\tests\unit\Config;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Config\Loaders\FileMatcher;

class FileMatcherTest extends Unit {
    const BASE = 'a.ext';

    public function testBasicMatch() {
        $dir = codecept_data_dir('filesys-misc');
        $subject = new FileMatcher(self::BASE, $dir);
        $this->assertEquals(['b'], $subject->getMatchingSuffixes());
    }
}
