<?php
namespace DinoTech\Phelix\Expressions;

use DinoTech\StdLib\Enum;

/**
 * The canonical token types within Phelix expressions.
 */
class TokenType extends Enum {
    const UNMAPPED   = 0;
    const WHITESPACE = 1;
    const ENCLOSING  = 2;
    const OPERATOR   = 4;
    const NUMBER     = 8;
    const STR        = 16;

    const STR_ESCAPE = 128;
    const ENCL_ARRAY = 256;
    const ENCL_MAP   = 512;
    const WS_NEWLINE = 1024;
}
