<?php
namespace DinoTech\Phelix\Expressions;

use DinoTech\Phelix\Expressions\Predicates\AndXOrPredicate;
use DinoTech\Phelix\Expressions\Predicates\EqualsPredicate;
use DinoTech\Phelix\Expressions\Predicates\GreaterThanPredicate;
use DinoTech\Phelix\Expressions\Predicates\LeafAPredicate;
use DinoTech\Phelix\Expressions\Predicates\LessThanPredicate;
use DinoTech\StdLib\Collections\ArrayUtils;

class PredicateFactory {
    const MAP = [
        'leafa' => LeafAPredicate::class,
        '==' => EqualsPredicate::class,
        '===' => EqualsPredicate::class,
        '!=' => EqualsPredicate::class,
        '>=' => GreaterThanPredicate::class,
        '>' => GreaterThanPredicate::class,
        '<=' => LessThanPredicate::class,
        '<' => LessThanPredicate::class,
        '&&' => AndXOrPredicate::class,
        '||' => AndXOrPredicate::class,
        'xor' => AndXOrPredicate::class,
    ];

    public static function fromOperator(string $operator, $left, $right) : PredicateInterface {
        $cls = ArrayUtils::get(self::MAP, $operator);
        if ($cls === null) {
            throw new \Exception("could not get predicate for $operator");
        }

        return new $cls($operator, $left, $right);
    }
}
