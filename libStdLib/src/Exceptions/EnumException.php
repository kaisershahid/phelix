<?php
namespace DinoTech\StdLib\Exceptions;

class EnumException extends \Exception {
    public static function notFound($cls, $given, array $possible) {
        $str = "$cls::$given not found. Did you mean: ";
        if (count($possible) > 10) {
            $list = array_slice($possible, 0, 10);
            $str .= implode(', ', $list);
            $str .= ", +" . (count($possible)-10) . ' others';
        } else {
            $str .= implode(', ', $possible);
        }

        return new self($str);
    }
}
