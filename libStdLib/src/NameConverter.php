<?php
namespace DinoTech\StdLib;

use DinoTech\StdLib\Collections\StandardList;

/**
 * Library to map to/from different naming conventions.
 * @todo move to Strings
 */
class NameConverter {
    public static function separatorToCamel($name, $separator) {
        $c = 0;
        return (new StandardList(explode($separator, $name)))
            ->map(function(KeyValue $kv) use (&$c) { return $c++ == 0 ? $kv->value() : ucfirst($kv->value()); })
            ->join('');
    }

    public static function dashToCamel($name) {
        return self::separatorToCamel($name, '-');
    }

    public static function snakeToCamel($name) {
        return self::separatorToCamel($name, '_');
    }

    const REGEX_CAMEL_CASE_SPLIT = '/([A-Z][a-z0-9]*)/';

    public static function camelToLowerCasedSeparated($name, $separator) {
        $tokens = preg_split(self::REGEX_CAMEL_CASE_SPLIT, $name, -1, PREG_SPLIT_DELIM_CAPTURE);
        return (new StandardList($tokens))
            ->filter(function(KeyValue $kv) { return !empty($kv->value()); })
            ->map('strtolower')
            ->join($separator);
    }

    public static function camelToDash($name) {
        return self::camelToLowerCasedSeparated($name, '-');
    }

    public static function classToDot($class) : string {
        return str_replace('\\', '.', $class);
    }
}
