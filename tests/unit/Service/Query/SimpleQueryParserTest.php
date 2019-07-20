<?php
namespace DinoTech\Phelix\tests\unit\Service\Query;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\Query\SimpleQueryParser;
use DinoTech\Phelix\Expressions\BasicContext;
use DinoTech\Phelix\Expressions\ExpressionLexer;
use DinoTech\Phelix\Expressions\ParserInterface;
use DinoTech\Phelix\Expressions\TokenSet;
use DinoTech\Phelix\Expressions\TokenSetInterface;
use DinoTech\Phelix\Expressions\TokenType;

// @todo move to functional
class SimpleQueryParserTest extends Unit {
    public function _getEvaluationScenarios() {
        return [
            'basic ==' => [
                'id == 5',
                ['id' => 5],
                true
            ],
            'or with true state' => [
                'id > 1 || rank <= 4',
                ['id' => 0, 'rank' => 3],
                true
            ],
            'or with false state' => [
                'id > 1 || rank <= 4',
                ['id' => 0, 'rank' => 8],
                false
            ],
            'nesting && predicate' => [
                '(id > 1 || rank <= 4) && name == "test"',
                ['id' => 0, 'rank' => 3, 'name' => 'test'],
                true
            ],
            'basic == nested.ref' => [
                'service.id == "com.dinotech.Service"',
                ['service' => ['id' => "com.dinotech.Service"]],
                true
            ],
            'operator precedence 1' => [
                'a || b && c',
                ['a' => 0, 'b' => 1, 'c' => 1],
                true
            ],
            'operator precedence 2' => [
                'a && b || c && d',
                ['a' => 1, 'b' => 0, 'c' => 1, 'd' => 1],
                true
            ],
        ];
    }

    /**
     * @param string $query
     * @param array $context
     * @param bool $expected
     * @dataProvider _getEvaluationScenarios
     */
    public function testEvaluation(string $query, array $context, bool $expected) {
        codecept_debug(">> $query");
        $ctx = new BasicContext($context);
        $subject = (new SimpleQueryParser($query));
        $statement = $subject
            ->getStatement()
            ->setContext($ctx);

        $result = $statement->executeStatement()->getResults();
        $this->assertEquals($expected, $result);
        $this->assertEquals($query, (string) $subject->getStatementBuilder()->getRoot());
    }
}
