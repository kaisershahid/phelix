<?php
namespace DinoTech\Phelix\tests\unit\Config;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Config\Loaders\MergeFromSubKeysProcessor;

class MergeFromSubKeysProcessorTest extends Unit {
    const ARR = [
        'properties' => [
            'includes-1' => [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'b' => 2.1
                ]
            ],
            'includes-2' => [
                'b' => 2.2
            ]
        ]
    ];

    public function testMergeWithIncludes1First() {
        $subject = (new MergeFromSubKeysProcessor(['properties'], ['includes-1', 'includes-2']))
            ->markKeyAsList('includes-1');

        $expected = ['properties' => ['a' => 1, 'b' => 2.2]];
        $actual = $subject->process(self::ARR);
        $this->assertEquals($expected, $actual);
    }

    public function testMergeWithIncludes2First() {
        $subject = (new MergeFromSubKeysProcessor(['properties'], ['includes-2', 'includes-1']))
            ->markKeyAsList('includes-1');

        $expected = ['properties' => ['a' => 1, 'b' => 2.1]];
        $actual = $subject->process(self::ARR);
        $this->assertEquals($expected, $actual);
    }
}
