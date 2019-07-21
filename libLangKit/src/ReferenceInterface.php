<?php
namespace DinoTech\LangKit;

/**
 * A general placeholder for tokens that define a literal value or dynamic
 * reference.
 */
interface ReferenceInterface {
    /**
     * Reference is a string.
     * @var int
     */
    const TYPE_STRING = 1;

    /**
     * Reference is a number.
     * @var int
     */
    const TYPE_NUMBER = 2;

    /**
     * Reference is a keyword.
     * @var int
     */
    const TYPE_KEYWORD = 3;

    /**
     * Reference defines some kind of structure.
     * @var int
     */
    const TYPE_STRUCTURE = 4;

    /**
     * Reference is dynamic class/variable.
     * @var int
     */
    const TYPE_DYNAMIC = 5;

    /**
     * Reference is a function call. (@todo can't this just be lumped with dynamic?)
     * @var int
     */
    const TYPE_FUNCTION = 6;

    public function getType() : int;

    /**
     * True if reference was originally wrapped quotes or any other string expression.
     * @return bool
     */
    public function isString() : bool;

    /**
     * True if reference doesn't match a scalar value.
     */
    public function isDynamic() : bool;

    /**
     * Casts reference string to its literal representation if possible. If reference
     * is dynamic, returns the original reference.
     * @return mixed
     */
    public function getLiteralValue();

    /**
     * Returns original string reference.
     * @return string
     */
    public function getRawValue() : string;
}
