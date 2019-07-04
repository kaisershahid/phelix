<?php
namespace DinoTech\StdLib\Collections;

interface SetCollection extends Collection {
    /**
     * Adds value to set if not already a member.
     * @param mixed $value
     * @return SetCollection
     */
    public function add($value) : SetCollection;
}
