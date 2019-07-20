<?php
namespace DinoTech\StdLib;

/**
 * Compares first value to second value and returns the following:
 *
 * 1. `1` if first is greater than second (or if first is not null and second is null)
 * 2. `-1` if first is less than second (or if first is null and second is not null)
 * 3. `0` if first and second are equal (including if both are null)
 */
interface Comparator {
    public function compare($first, $second) : int;
}
