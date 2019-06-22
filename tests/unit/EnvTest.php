<?php
namespace DinoTech\Phelix\tests\unit;

use Codeception\Test\Unit;
use DinoTech\Phelix\Env;

class EnvTest extends Unit {
    public function testMode() {
        $env = new Env('test');
        $this->assertTrue($env->is('test'));
    }

    public function testModeAndModifiers() {
        $env = new Env('test.a.z.g.1');
        $this->assertTrue($env->is('test.a'));
        $this->assertTrue($env->is('test.z.1.a'));
        $this->assertFalse($env->is('test.z.1.2'));
    }
}
