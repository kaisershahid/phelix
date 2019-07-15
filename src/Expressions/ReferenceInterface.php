<?php
namespace DinoTech\Phelix\Expressions;

/**
 * A general placeholder for tokens that define a literal value or dynamic
 * reference.
 */
interface ReferenceInterface {
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
