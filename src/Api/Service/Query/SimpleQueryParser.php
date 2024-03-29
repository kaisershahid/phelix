<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\LangKit\ExpressionLexer;
use DinoTech\LangKit\ParserInterface;
use DinoTech\LangKit\StatementInterface;
use DinoTech\LangKit\TokenMapper;
use DinoTech\LangKit\TokenSetInterface;
use DinoTech\Phelix\Expressions\TokenSet;
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
    }

    public function getStatementBuilder() : StatementBuilder {
        return $this->predBuilder;
    }

    public function getStatement() : StatementInterface {
        $pred = (new NTreePredicateBuilder($this->predBuilder->getRoot()))
            ->getPredicate();
        return new Statement($pred);
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
            self::$refTokenMapper = TokenMapper::fromEnumNames(TokenSet::class, [
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
