<?php
namespace DinoTech\Phelix\Expressions;

use DinoTech\StdLib\Enum;

/**
 * The canonical token sets for Phelix -- these relate specifically to the various
 * expression syntaxes used within the framework.
 */
class TokenSet extends Enum implements TokenSetInterface {
    const GROUPING  = ['\(|\)', TokenType::ENCLOSING];
    const STRUCTURE = ['\{|\}', TokenType::ENCLOSING];
    const INDEX     = ['\[|\]|', TokenType::ENCLOSING];

    const LOGICAL_OPERATORS    = ['!=|!|==|&&|\|\||>=|<=|>|<|xor', TokenType::OPERATOR];
    const MATH_OPERATORS       = ['-|\+|\*|\/', TokenType::OPERATOR];
    const BITWISE_OPERATORS    = ['\^|~|<<|>>|\||&', TokenType::OPERATOR];
    const UNARY_OPERATORS      = ['--|\+\+', TokenType::OPERATOR];
    const TERNARY_OPERATORS    = ['\?|:', TokenType::OPERATOR];
    const ASSIGNMENT_OPERATORS = ['=', TokenType::OPERATOR];
    const ARGUMENT_OPERATORS   = [',', TokenType::OPERATOR];

    const EMBEDDED = ['\$\{|\}', TokenType::UNMAPPED];
    const ESCAPE   = ['\\\\', TokenType::STR];
    const QUOTE    = ["'|\"", TokenType::STR];
    const SPACE    = [" |\t", TokenType::WHITESPACE];
    const NEWLINE  = ["\r\n|\r|\n", TokenType::WHITESPACE];
    const FLOATING = ['\d+\.\d+|\de-?\d+', TokenType::NUMBER];
    const INTEGER  = ['\d+', TokenType::NUMBER];

    /** @var string */
    private $regex;
    /** @var int */
    private $type;

    protected function __postConstruct() {
        $val = $this->value();
        $this->regex = $val[0];
        $this->type = $val[1];
    }

    public function regex() : string {
        return $this->regex;
    }

    public function tokens(): array {
        return preg_split('#(?<!\\\\)\|#', $this->regex);
    }

    public function isType(int $flag): bool {
        return ($this->type & $flag) === $flag;
    }

    public function isEnclosing() : bool {
        return $this->isType(TokenType::ENCLOSING);
    }

    public function isOperator() : bool {
        return $this->isType(TokenType::OPERATOR);
    }

    public function isString() : bool {
        return $this->isType(TokenType::STR);
    }

    public function isWhitespace() : bool {
        return $this->isType(TokenType::WHITESPACE);
    }
}

