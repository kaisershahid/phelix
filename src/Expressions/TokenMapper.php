<?php
namespace DinoTech\Phelix\Expressions;

use DinoTech\StdLib\Collections\ArrayUtils;

/**
 * Helper class that generates a master token pattern from 1 or more `TokenSets`
 * enums and return the enum associated with a specific token.
 * @todo tests (esp around checked tokens that are escaped `|| -> \|\|`)
 */
class TokenMapper {

    /**
     * @param string[] $names
     * @return TokenMapper
     * @throws \DinoTech\StdLib\Exceptions\EnumException
     */
    public static function fromNames(array $names) : TokenMapper {
        $enums = [];
        foreach ($names as $name) {
            $enums[] = TokenSet::fromName($name);
        }

        return new self($enums);
    }

    /**
     * @param TokenSetInterface[] $tokenSets
     * @return TokenMapper
     */
    public static function fromEnums(array $tokenSets) : TokenMapper {
        return new self($tokenSets);
    }

    private $map = [];
    private $regex;

    /**
     * @param TokenSetInterface[] $tokenSets
     * @todo check type
     */
    public function __construct(array $tokenSets, $delim = '#') {
        $regex = [];
        foreach ($tokenSets as $tokenSet) {
            $regex[] = $tokenSet->regex();
            foreach ($tokenSet->tokens() as $token) {
                $tkn = preg_replace('#\\\\(.)#', '$1', $token);
                $this->map[$tkn] = $tokenSet;
            }
        }

        $this->regex = $delim . '(' . implode('|', $regex) . ')' . $delim;
    }

    public function getRegex() : string {
        return $this->regex;
    }

    public function getEnum(string $token) : ?TokenSetInterface {
        return ArrayUtils::get($this->map, $token);
    }
}
