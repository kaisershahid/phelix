<?php
namespace DinoTech\Phelix\tests\unit\Service\Query;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\Query\QueryReference;

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
        $subject = new QueryReference($original);
        $this->assertEquals($expect, $subject->getLiteralValue());
    }
}
