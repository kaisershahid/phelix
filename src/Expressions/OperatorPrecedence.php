<?php
namespace DinoTech\Phelix\Expressions;

use DinoTech\StdLib\Collections\ArrayUtils;

class OperatorPrecedence {
    const MAP_L2R = [
        '!' => 16,
        '~' => 16,
        '*' => 14,
        '/' => 14,
        '%' => 14,
        '+' => 13,
        '-' => 13,
        '>>' => 12,
        '<<' => 12,
        '<' => 11,
        '>' => 11,
        '<=' => 11,
        '>=' => 11,
        '==' => 10,
        '!=' => 10,
        '===' => 10,
        '!==' => 10,
        '&' => 9,
        '^' => 8,
        '|' => 7,
        '&&' => 6,
        '||' => 5,
        '?' => 4,
        '=' => 3,
        ',' => 1
    ];

    const MAP_R2L = [
        '--' => 17,
        '++' => 17,
    ];

    public static function getL2R($op) {
        return ArrayUtils::get(self::MAP_L2R, $op, 0);
    }

    public static function compareL2R($op1, $op2) {
        $v1 = ArrayUtils::get(self::MAP_L2R, $op1 ?: '', 0);
        $v2 = ArrayUtils::get(self::MAP_L2R, $op2 ?: '', 0);
        if ($v1 > $v2) {
            return 1;
        } else if ($v1 < $v2) {
            return -1;
        } else {
            return 0;
        }
    }
}
