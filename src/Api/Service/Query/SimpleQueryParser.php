<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\Phelix\Expressions\ExpressionLexer;
use DinoTech\Phelix\Expressions\ParserInterface;
use DinoTech\Phelix\Expressions\TokenMapper;
use DinoTech\Phelix\Expressions\TokenSet;
use DinoTech\Phelix\Expressions\TokenSetInterface;
use DinoTech\Phelix\Expressions\TokenType;

class SimpleQueryParser implements ParserInterface {
    private $raw;
    private $preds;
    private $predBuilder;
    private $statement;

    public function __construct(string $query) {
        $this->raw = $query;
        $this->preds = [];

        $this->predBuilder = new StatementBuilder();
        (new ExpressionLexer(self::getTokenMapper()))->lex($query, $this);
        $this->statement = $this->predBuilder->getRoot()->rebalance();
    }

    public function getStatement() {
        return $this->statement;
    }

    /**
     * @return array
     */
    public function getPredicates() {
        return $this->preds;
    }

    /**
     * @param string $token
     * @param TokenSet $tokenSet
     * @throws \Exception
     */
    public function processToken(string $token, TokenSetInterface $tokenSet) {
        if ($tokenSet->isType(TokenType::ENCLOSING)) {
            $this->predBuilder = $this->predBuilder->pushGrouping($token);
        } else if ($tokenSet->isOperator()) {
            $this->predBuilder = $this->predBuilder->pushOperator($token);
        } else if ($tokenSet->isType(TokenType::STR)) {
            $this->predBuilder = $this->predBuilder->pushQuote($token);
        } else if ($tokenSet->isType(TokenType::WHITESPACE)) {
            $this->predBuilder = $this->predBuilder->pushSpace($token);
        }
    }

    public function processChars(string $chars) {
        $this->predBuilder = $this->predBuilder->pushChars($chars);
    }

    public function terminate() {
        $this->predBuilder->cleanup();
    }

    private static $refTokenMapper;

    public static function getTokenMapper() {
        // @todo make TokenType enum?
        if (self::$refTokenMapper == null) {
            self::$refTokenMapper = TokenMapper::fromNames([
                'GROUPING',
                'LOGICAL_OPERATORS',
                'MATH_OPERATORS',
                'QUOTE',
                'ESCAPE',
                'SPACE'
            ]);
        }

        return self::$refTokenMapper;
    }
}
