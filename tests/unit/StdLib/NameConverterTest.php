<?php
namespace DinoTech\Phelix\tests\unit\StdLib;

use Codeception\Test\Unit;
use DinoTech\Phelix\StdLib\NameConverter;

class NameConverterTest extends Unit {
    public function testDashToCamel() {
        $source = 'a-name-to-convert';
        $expected = 'aNameToConvert';
        $this->assertEquals($expected, NameConverter::dashToCamel($source));
    }

    public function testCamelToDash() {
        $source = 'aNameToConvert';
        $expected = 'a-name-to-convert';
        $this->assertEquals($expected, NameConverter::camelToDash($source));
    }
}
