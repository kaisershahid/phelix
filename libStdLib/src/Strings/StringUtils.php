<?php
namespace DinoTech\StdLib\Strings;

class StringUtils {
    public static function contains($needle, $haystack) : bool {
        return strpos($haystack, $needle) !== false;
    }

    public static function indexOf($needle, $haystack, $offset = 0) : ?int {
        $pos = strpos($haystack, $needle, $offset);
        if ($pos === false) {
            return null;
        }

        return $pos;
    }
}
