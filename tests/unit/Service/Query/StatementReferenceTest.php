<?php
namespace DinoTech\Phelix\tests\unit\Service\Query;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\Query\StatementReference;

class StatementReferenceTest extends Unit {
    public function _getValues() {
        return [
            'string' => [
                'a',
                'a'
            ],
            'int' => [
                '5',
                5
            ],
            'float -1.1' => [
                '-1.1',
                -1.1
            ],
            'float 1.5e10' => [
                '1.5e10',
                1.5e10
            ]
        ];
    }

    /**
     * @dataProvider _getValues
     */
    public function testValueTransformations($original, $expect) {
        $subject = new StatementReference($original);
        $this->assertEquals($expect, $subject->getValue());
    }
}
