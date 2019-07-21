<?php
namespace DinoTech\LangKit;

/**
 * A reference is a lexeme that's either a scalar or reference to a variable,
 * constant, etc.
 */
interface ReferenceInterface {
    /**
     * Returns an int id representing the type.
     * @return int
     */
    public function getType() : int;

    /**
     * Checks if this reference is the given type.
     * @param int $type
     * @return bool
     */
    public function isType(int $type) : bool;

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

    /**
     * Returns the literal or evaluated value held by the reference.
     * @param ContextInterface $context
     * @return mixed
     */
    public function evaluate(ContextInterface $context);
}
