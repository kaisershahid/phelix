<?php
namespace DinoTech\StdLib\Collections;

/**
 * Standard operations for a map-style collection.
 */
interface MapCollection extends Collection {
    public function pluckKeys(string ...$keys) : MapCollection;
}
