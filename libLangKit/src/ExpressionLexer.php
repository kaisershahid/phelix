<?php
namespace DinoTech\LangKit;

use DinoTech\StdLib\Collections\ArrayUtils;

class ExpressionLexer {
    /** @var TokenMapper */
    private $tokenMapper;

    /**
     * @param TokenSetInterface[] $tokenEnums
     */
    public function __construct(TokenMapper $tokenMapper) {
        $this->tokenMapper = $tokenMapper;
    }

    public function lex(string $expression, ParserInterface $parser) {
        $tokens = preg_split(
            $this->tokenMapper->getRegex(),
            $expression,
            -1,
            PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE
        );

        foreach ($tokens as $token) {
            $enum = $this->tokenMapper->getEnum($token);
            if ($enum) {
                $parser->processToken($token, $enum);
            } else {
                $parser->processChars($token);
            }
        }

        $parser->terminate();
    }
}
