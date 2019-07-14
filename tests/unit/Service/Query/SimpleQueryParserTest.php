<?php
namespace DinoTech\Phelix\tests\unit\Service\Query;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Service\Query\SimpleQueryParser;
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

    const QUERY1 = "key > 5";
    const QUERY2 = "service.id == 'something \' escape' || service.rank > 5";

    public function _getQueries() {
        return [
            self::QUERY1 => [
                self::QUERY1,
                [
                    'left' => 'key',
                    'op' => '>',
                    'right' => 5
                ]
            ],
            self::QUERY2 => [
                self::QUERY2,
                [
                    'left' => [
                        'left' => 'service.id',
                        'op' => '==',
                        'right' => '"something \\\' escape"'
                    ],
                    'op' => '||',
                    'right' => [
                        'left' => 'service.rank',
                        'op' => '>',
                        'right' => 5
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider _getQueries
     */
    public function testParsing(string $query, array $expected) {
        $query = new SimpleQueryParser($query);
        $stmt = $query->getStatement();
        $this->assertArraySubset($expected, $stmt->jsonSerialize());
    }

}
