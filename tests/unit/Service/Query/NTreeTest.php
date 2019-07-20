<?php
namespace DinoTech\Phelix\tests\unit\Service\Query;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\Query\NTree;
use DinoTech\Phelix\Api\Service\Query\NTreeNode;
use DinoTech\Phelix\Expressions\OperatorPrecedence;

class NTreeTest extends Unit {
    public function testValueOperandValue() {
        $tree = new NTree();
        $tree->pushValue('a')->pushOperator('+')->pushValue('b');
        $this->assertEquals(
            [
                ['value' => 'a', 'type' => NTree::VAL],
                ['value' => '+', 'type' => NTree::OP],
                ['value' => 'b', 'type' => NTree::VAL]
            ],
            $tree->jsonSerialize()
        );
    }

    public function testPopLastSetsCorrectOperator() {
        $tree = (new NTree())->setComparator(new OperatorPrecedence());
        $tree->pushValue('a')->pushOperator('+')->pushValue('b')
            ->pushOperator('-');
        $last = $tree->popLast();
        $this->assertEquals('+', $tree->getLastOp());
    }

    /**
     * `a + b * c` => `a + (b * c)`
     * @throws \Exception
     */
    public function testRebalanceByOperatorPrecedenceRotateRight() {
        $tree = (new NTree())->setComparator(new OperatorPrecedence());
        $lastTree = $tree
            ->pushValue('a')->pushOperator('+')->pushValue('b')
            ->pushOperator('*')->pushValue('c')
        ;

        $expectedSub = [
            ['value' => 'b', 'type' => NTree::VAL],
            ['value' => '*', 'type' => NTree::OP],
            ['value' => 'c', 'type' => NTree::VAL]
        ];
        $this->assertEquals($expectedSub, $lastTree->jsonSerialize());

        $expectedRoot = [
            ['value' => 'a', 'type' => NTree::VAL],
            ['value' => '+', 'type' => NTree::OP],
            ['value' => $lastTree->jsonSerialize(), 'type' => NTree::VAL]
        ];
        $this->assertEquals($expectedRoot, $tree->jsonSerialize());
        $this->assertEquals($tree, $lastTree->getParent());
    }

    public function testRebalanceByOperatorPrecedenceRotateLeft() {
        $tree = (new NTree())->setComparator(new OperatorPrecedence());
        $lastTree = $tree
            ->pushValue('a')->pushOperator('*')->pushValue('b')
            ->pushOperator('+')->pushValue('c')
        ;

         $expectedRoot = [
            [
                'value' => [
                    ['value' => 'a', 'type' => NTree::VAL],
                    ['value' => '*', 'type' => NTree::OP],
                    ['value' => 'b', 'type' => NTree::VAL],
                ],
                'type' => NTree::VAL
            ],
            ['value' => '+', 'type' => NTree::OP],
            ['value' => 'c', 'type' => NTree::VAL],
        ];
        $this->assertEquals($expectedRoot, $tree->jsonSerialize());
    }
}
