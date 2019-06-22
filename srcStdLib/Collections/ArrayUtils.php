<?php
namespace DinoTech\StdLib\Collections;

use Consistence\Type\Type;

/**
 * Contains static utility methods that operate on basic arrays.
 */
final class ArrayUtils {
    private function __construct() {
    }

    public static function slice(array $arr, $firstKey, $lastKey, $max = 0) : array {
        $out = [];
        $count = 0;
        $found = false;
        foreach ($arr as $key => $val) {
            if ($key == $firstKey) {
                $found = true;
            }

            if ($found) {
                $out[$key] = $val;
                $count++;
            }

            if (($max > 0 && $max == $count) || $key == $lastKey) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param array|\ArrayAccess $arr
     * @param string $offset
     * @param mixed $default
     * @return mixed
     */
    public static function get($arr, string $offset, $default = null) {
        if (static::isSet($arr, $offset)) {
            return $arr[$offset];
        }

        return $default;
    }

    /**
     * Given a delimited key, attempt to find the longest matching key for the
     * given array, otherwise, check the first level of keys for remaining keys.
     * For instance, given `a.b.c.d`, check:
     *
     * - `a.b.c.d`
     * - `b.c.d` in `[a]`
     * - `c.d` in `[a][b]`
     * - `d` in `[a][b][c]`
     *
     * If expected key is not found at any level, the default is returned.
     *
     * @param array|\ArrayAccess $arr
     * @param string $key
     * @param string $separator
     * @param mixed $default
     * @return mixed
     */
    public static function getNested($arr, string $key, $separator = '.', $default = null) {
        if ($key === null) {
            return $arr;
        } else if (static::isSet($arr, $key)) {
            return $arr[$key];
        }

        $splitKeys = explode($separator, $key, 2);
        $first = array_shift($splitKeys);
        $next = array_shift($splitKeys);
        if (static::isSet($arr, $first)) {
            return static::getNested($arr[$first], $next, $separator, $default);
        }

        return $default;
    }

    /**
     * Similar to `getNested`, except sets a deep value. Created sub-arrays as
     * necessary. Ensure that an `\ArrayAccess` can be properly used with the
     * reference operator.
     * @param array|\ArrayAccess $arr
     * @param string $key
     * @param mixed $value
     * @param string $separator
     */
    public static function setNested(&$arr, string $key, $value, $separator = '.') {
        if ($key === null) {
            return;
        } else if (static::isSet($arr, $key)) {
            $arr[$key] = $value;
            return;
        }

        $splitKeys = explode($separator, $key, 2);
        $first = array_shift($splitKeys);
        $next = array_shift($splitKeys);
        if (!$next) {
            $arr[$first] = $value;
            return;
        } else if (!static::isSet($arr, $first)) {
            $arr[$first] = [];
        }

        static::setNested($arr[$first], $next, $value, $separator);
    }

    /**
     * @param array|\ArrayAccess $arr
     * @param string $key
     * @return bool
     */
    public static function isSet($arr, string $key) : bool {
        if (is_array($arr)) {
            return isset($arr[$key]);
        } else if ($arr instanceof \ArrayAccess) {
            return $arr->offsetExists($key);
        }

        return false;
    }

    /**
     * @param array $source
     * @param array $target
     * @param bool $strict
     * @return DiffEntry[]|ListCollection
     */
    public static function diff(array $source, array $target, $strict = true) : ListCollection {
        $diffs = [];

        foreach ($source as $key => $val) {
            $tval = self::get($target, $key);
            if (!self::isEqual($val, $tval, $strict)) {
                $diffs[$key] = new DiffEntry($key, $val, $tval);
            }
        }

        foreach ($target as $key => $tval) {
            $val = self::get($source, $key);
            if (!self::isEqual($val, $tval, $strict)) {
                $diffs[$key] = new DiffEntry($key, $val, $tval);
            }
        }

        return new ReadOnlyList($diffs);
    }

    public static function union(array $source, array $target, $strict = true) : MapCollection {
        $union = [];
        foreach ($source as $key => $val) {
            if (self::isEqual($val, self::get($target, $key), $strict)) {
                $union[$key] = $val;
            }
        }

        return new ReadOnlyMap($union);
    }

    public static function isEqual($val, $otherVal, bool $strict = true) : bool {
        return $strict ? $val === $otherVal : $val == $otherVal;
    }

    /**
     * Casts input to array if it matches support types.
     * @param array|Collection $input
     * @return array
     * @throws UnsupportedOperationException
     */
    public static function toArray($input) : array {
        try {
            Type::checkType($input, 'array|' . Collection::class);
        } catch (\Consistence\InvalidArgumentTypeException $e) {
            throw new UnsupportedOperationException($e->getMessage(), $e->getCode());
        }

        $arr = $input;
        if ($input instanceof Collection) {
            $arr = $input->jsonSerialize();
        }

        return $arr;
    }
}
