<?php
namespace DinoTech\Phelix\tests\unit;

use Codeception\Test\Unit;
use DinoTech\Phelix\Framework;

/**
 * @property $tester UnitTester
 */
class FrameworkTest extends Unit {
    /** @var Framework */
    protected $framework;

    public function _before() {
        $this->framework = (new Framework('test.mod.local'))
            ->setRoot(codecept_data_dir() . '/framework')
            ->setConfigFile('config.yml')
            ->boot()
        ;
    }

    const EXPECTED_CONFIG = [
        'properties' => [
            'config-scope' => 'test',
            'prop1' => 1,
            'prop2' => 'value2'
        ],
        'bundlesBoot' => [
            './boot'
        ],
        'bundleRoots' => [
            './3rd-party'
        ],
        'framework' => [
            'path.cache' => './var/cache'
        ]
    ];

    public function testBoot() {
        codecept_debug($this->framework->getConfiguration()->jsonSerialize());
        $this->assertArraySubset(self::EXPECTED_CONFIG,
            $this->framework->getConfiguration()->jsonSerialize());
    }
}
