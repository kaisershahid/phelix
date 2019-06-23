<?php
namespace DinoTech\Phelix\tests\stdlib\Collections;

use Codeception\Test\Unit;
use DinoTech\StdLib\Collections\ArrayUtils;

class ArrayUtilsTest extends Unit {
    const ARR_GET = [
        'a' => [
            'b' => [
                'c' => 1,
                'd' => 2
            ],
            'b:e' => 5,
        ],
        'a:b:d' => 10
    ];

    public function _getGetScenarios() {
        return [
            'a:b' => ['a:b', ['c' => 1, 'd' => 2]],
            'a:b:c' => ['a:b:c', 1],
            'a:b:d' => ['a:b:d', 10],
            'a:b:e' => ['a:b:e', 5]
        ];
    }

    /**
     * @param string $key
     * @param $expectedValue
     * @dataProvider _getGetScenarios
     */
    public function testGet(string $key, $expectedValue) {
        $value = ArrayUtils::getNested(self::ARR_GET, $key, ':');
        $this->assertEquals($expectedValue, $value, "failed get key '$key'");
    }

    /**
     * @param string $key
     * @param $expectedValue
     * @dataProvider _getGetScenarios
     */
    public function testSet(string $key, $expectedValue) {
        $arr = [];
        ArrayUtils::setNested($arr, $key, $expectedValue, ':');
        $this->assertEquals($expectedValue, ArrayUtils::getNested($arr, $key, ':'));
    }

    const ARR1 = [
        'str1' => 'str1',
        'arr1_append_scalar' => [1, 2],
        'arr2_append_scalar' => 9,
        'arr3_merge' => ['a' => 1, 'b' => 2]
    ];

    const ARR2 = [
        'str1' => 'str2-overwrite',
        'arr1_append_scalar' => 3,
        'arr2_append_scalar' => [7, 8],
        'arr3_merge' => ['b' => 0.2, 3],
        'str2' => 'str2'
    ];

    const ARR_MERGED = [
        'str1' => 'str2-overwrite',
        'arr1_append_scalar' => [1,2, 3],
        'arr2_append_scalar' => [7, 8, 9],
        'arr3_merge' => ['a' => 1, 'b' => 0.2, 0 => 3],
        'str2' => 'str2'
    ];

    public function testMerge() {
        $arr = ArrayUtils::merge(self::ARR1, self::ARR2);
        $this->assertEquals(self::ARR_MERGED,
            ArrayUtils::merge(self::ARR1, self::ARR2));
    }
}
