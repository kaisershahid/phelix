<?php
namespace DinoTech\LangKit;

/**
 * Represents a very high-level parser that accepts either a pre-defined token or
 * a stream of characters.
 */
interface ParserInterface {
    public function processToken(string $token, TokenSetInterface $tokenSet);

    public function processChars(string $chars);

    public function terminate();
}
