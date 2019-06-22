<?php
namespace DinoTech\StdLib\Collections;

/**
 * Standard operations for a list-style collection.
 */
interface ListCollection extends Collection {
    /**
     * Adds value to end of list.
     * @param $value
     * @return ListCollection
     */
    public function push($value) : ListCollection;

    /**
     * Removes last value from list.
     * @return mixed
     */
    public function pop();

    /**
     * Removes first value from list.
     * @return mixed
     */
    public function shift();

    /**
     * Adds value to beginning of list.
     * @param $val
     * @return ListCollection
     */
    public function unshift($val) : ListCollection;
}
