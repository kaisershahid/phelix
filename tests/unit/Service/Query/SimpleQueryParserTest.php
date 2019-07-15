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

class SimpleQueryParserTest extends Unit implements ParserInterface {
    /** @var array */
    private $buff;

    public function _before() {
        $this->buff = [];
    }

    public function processChars(string $chars) {
        $this->buff[] = $chars;
    }

    public function processToken(string $token, TokenSetInterface $tokenSet) {
        $this->buff[] = [$token, $tokenSet];
    }

    public function terminate() {
    }

    const EXPR_GROUPINGS = '(a=1)';

    /**
     * @skip
     */
    public function testGroupings() {
        $expect = [
            ['(', TokenSet::GROUPING()],
            'a',
            ['=', TokenSet::LOGICAL_OPERATORS()],
            '1',
            [')', TokenSet::GROUPING()],
        ];
        (new ExpressionLexer(SimpleQueryParser::getTokenMapper()))->lex(self::EXPR_GROUPINGS, $this);
        $this->assertEquals($expect, $this->buff);
    }

    const EXPR_OPERATORS = '!==!||&&abcxor';

    /**
     * @skip
     */
    public function testOperators() {
        $op = TokenSet::LOGICAL_OPERATORS();
        $expect = [
            ['!=', $op],
            ['=', $op],
            ['!', $op],
            ['||', $op],
            ['&&', $op],
            'abc',
            ['xor', $op]
        ];
        (new ExpressionLexer(SimpleQueryParser::getTokenMapper()))->lex(self::EXPR_OPERATORS, $this);
        $this->assertEquals($expect, $this->buff);
    }

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
        $ctx = new BasicContext($context);
        $statement = (new SimpleQueryParser($query))
            ->getStatement()
            ->setContext($ctx);
        $result = $statement->executeStatement()->getResults();
        $this->assertEquals($expected, $result);
    }
}
