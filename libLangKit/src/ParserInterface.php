<?php
namespace DinoTech\LangKit;

/**
 * Represents a very high-level parser that accepts either a pre-defined token or
 * a stream of characters.
 */
interface ParserInterface {
    /**
     * A recognized token.
     * @param string $token
     * @param TokenSetInterface $tokenSet
     */
    public function processToken(string $token, TokenSetInterface $tokenSet);

    /**
     * A non-token set of characters.
     * @param string $chars
     */
    public function processChars(string $chars);

    /**
     * Signals end of input.
     */
    public function terminate();
}
