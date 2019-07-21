<?php
namespace unit\Predicates;

use Codeception\Test\Unit;
use DinoTech\LangKit\ContextInterface;
use DinoTech\LangKit\Predicates\StandardUnaryOperator;
use DinoTech\LangKit\ReferenceInterface;

class StandardUnaryOperatorTest extends Unit {
    public function _getBasicUnary() {
        return [
            '-' => ['-', 1, -1],
            '+' => ['+', 1, 1],
            '!false' => ['!', false, true],
            '!true' => ['!', true, false]
        ];
    }

    /**
     * @param $op
     * @param $value
     * @param $expected
     * @dataProvider _getBasicUnary
     */
    public function testBasic($op, $value, $expected) {
        $subject = new StandardUnaryOperator();
        $val = $subject->evaluatePrefix($op, $value, \Mockery::mock(ContextInterface::class));
        $this->assertEquals($expected, $val);
    }

    public function _getPrefixCrement() {
        return [
            '--' => ['--', 1, 1, 0],
            '++' => ['++', 1, 1, 2],
        ];
    }

    /**
     * @param $op
     * @param $value
     * @param $expected
     * @param $expectedNew
     * @throws \DinoTech\LangKit\UnaryOperatorException
     * @dataProvider _getPrefixCrement
     */
    public function testPrefixCrement($op, $value, $expected, $expectedNew) {
        $subject = new StandardUnaryOperator();

        $ref = \Mockery::mock(ReferenceInterface::class);
        $ref->shouldReceive([
            'evaluate' => $value,
            'getRawValue' => 'key'
        ]);
        $ctx = \Mockery::mock(ContextInterface::class);
        $ctx->shouldReceive('setVar')
            ->withArgs(['key', $expectedNew]);

        $val = $subject->evaluatePrefix($op, $ref, $ctx);
        $this->assertEquals($expected, $val);
    }

    public function _getPostfixCrement() {
        return [
            '--' => ['--', 1, 0, 0],
            '++' => ['++', 1, 2, 2],
        ];
    }

    /**
     * @param $op
     * @param $value
     * @param $expected
     * @param $expectedNew
     * @throws \DinoTech\LangKit\UnaryOperatorException
     * @dataProvider _getPrefixCrement
     */
    public function testPostfixCrement($op, $value, $expected, $expectedNew) {
        $subject = new StandardUnaryOperator();

        $ref = \Mockery::mock(ReferenceInterface::class);
        $ref->shouldReceive([
            'evaluate' => $value,
            'getRawValue' => 'key'
        ]);
        $ctx = \Mockery::mock(ContextInterface::class);
        $ctx->shouldReceive('setVar')
            ->withArgs(['key', $expectedNew]);

        $val = $subject->evaluatePrefix($op, $ref, $ctx);
        $this->assertEquals($expected, $val);
    }
}
