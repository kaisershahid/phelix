<?php
namespace DinoTech\LangKit;

/**
 * Groups together 1 or more tokens that belong to a general class. For instance,
 * `==` and `<` can be grouped as logical operators, while `+` and `-` can be
 * grouped as mathematical operators.
 *
 * A token set can represent multiple types, going from generic to granular (e.g.
 * `whitespace > newline`. Checks against the token type will be done by supplying
 * a bit field.
 */
interface TokenSetInterface {
    /**
     * The human-friendly name of the token set.
     * @return string
     */
    public function name() : string;

    /**
     * Returns a regular expression pattern WITHOUT delimeters.
     * @return string
     */
    public function regex() : string;

    /**
     * Splits up delimited list of tokens.
     * @return array
     */
    public function tokens(): array;

    /**
     *
     * @param int $flag
     * @return bool
     */
    public function isType(int $flag) : bool;
}
