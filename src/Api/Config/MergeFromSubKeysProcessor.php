<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\ArrayUtils;

/**
 * Takes an input array and crawls structure. If specific keys are encountered,
 * merge the contents of that key into the current level non-recursively.
 *
 * For instance, if `includes` is one of the keys, the following scenario would
 * play out:
 *
 * ```yaml
 * --- # before
 * properties;
 *   includes:
 *     - key: val
 *       key2: val
 *     - key2: val2
 *
 * --- # after
 * properties:
 *   key: val
 *   key2: val2
 * ```
 *
 * The order of mergeable keys determines final value: given `['a', 'b']`, any
 * key under `b` will overwrite the same key in `a`.
 */
class MergeFromSubKeysProcessor {
    /**
     * The top-level keys to check for mergeable keys. Keys can be nested through
     * `key:subkey:...` syntax.
     * @var array
     */
    protected $checkKeys;

    /**
     * The keys that trigger merging. Keys CANNOT be nested.
     * @var array
     */
    protected $mergeKeys;

    /**
     * For any key in `$mergeKeys` that's also in this list, the array value of
     * the key is assumed to be a list of maps instead of a map.
     * @var array
     */
    protected $mergeKeysAsList = [];

    public function __construct(array $checkKeys, array $mergeKeys) {
        $this->checkKeys = $checkKeys;
        $this->mergeKeys = $mergeKeys;
    }

    /**
     * Values within the key will be treated as list of maps.
     * @param string $key
     * @return MergeFromSubKeysProcessor
     */
    public function markKeyAsList(string $key) : MergeFromSubKeysProcessor {
        $this->mergeKeysAsList[$key] = true;
        return $this;
    }

    /**
     * Values within the keys will be treated as list of maps.
     * @param array $keys
     * @return MergeFromSubKeysProcessor
     */
    public function markKeysAsList(array $keys) : MergeFromSubKeysProcessor {
        foreach ($keys as $key) {
            $this->mergeKeysAsList[$key] = true;
        }

        return $this;
    }

    /**
     * @param array $input
     * @return array
     */
    public function process(array $input) : array {
        $output = $input;
        foreach ($this->checkKeys as $key) {
            $sub = ArrayUtils::getNested($output, $key, ':');
            if (is_array($sub)) {
                ArrayUtils::setNested($output, $key, $this->processMergeKeys($sub), ':');
            }
        }

        return $output;
    }

    public function processMergeKeys(array $arr) : array {
        foreach ($this->mergeKeys as $key) {
            $merge = ArrayUtils::get($arr, $key);
            if (is_array($merge)) {
                $arr = $this->doMerge($key, $arr, $merge);
                unset($arr[$key]);
            }
        }

        return $arr;
    }

    /**
     * Merges a map or list of maps with input array.
     * @param string $key
     * @param array $arr
     * @param array $merge
     * @return array The merged map.
     */
    public function doMerge(string $key, array $arr, array $merge) {
        if (isset($this->mergeKeysAsList[$key])) {
            return array_merge($arr, self::combineList($merge));
        } else {
            return array_merge($arr, $merge);
        }
    }

    /**
     * For each value in list, if the value is a map, merge into an accumulator
     * array.
     * @param array $list
     * @return array
     */
    public static function combineList(array $list) {
        $arr = [];
        foreach ($list as $value) {
            if (is_array($value)) {
                $arr = array_merge($arr, $value);
            }
        }

        return $arr;
    }
}
