<?php
namespace DinoTech\Phelix\tests\unit\StdLib;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\LifecycleStatus;
use DinoTech\StdLib\Exceptions\EnumException;
use DinoTech\StdLib\KeyValue;

class EnumTest extends Unit {
    public function testEnumsActuallyWorkAndAlsoIteratingOverValues() {
        $expected = [
            'DISABLED' => 'disabled',
            'STARTING' => 'starting',
            'ERROR' => 'error',
            'UNSATISFIED' => 'unsatisfied'
        ];

        $actual = LifecycleStatus::values()->map(function(KeyValue $kv) {
            return $kv->value()->value();
        })->slice('DISABLED', 'UNSATISFIED')->jsonSerialize();

        $this->assertEquals($expected, $actual);
    }

    public function testEnumExceptionNotFound() {
        $e = null;
        try {
            LifecycleStatus::fromName('test');
            $this->assertTrue(false, 'expected exception');
        } catch (EnumException $e) {
            $this->assertEquals(
                "DinoTech\Phelix\Api\Service\LifecycleStatus::test not found. Did you mean: DISABLED, STARTING, ERROR, UNSATISFIED, SATISFIED, ACTIVE",
                $e->getMessage()
            );
        }
    }

    public function testStaticGetter() {
        $expected = LifecycleStatus::fromName('active');
        $actual = LifecycleStatus::ACTIVE();
        $this->assertEquals($expected, $actual);
    }
}
