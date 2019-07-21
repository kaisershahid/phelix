<?php
namespace DinoTech\LangKit;

use DinoTech\LangKit\Predicates\AndXOrPredicate;
use DinoTech\LangKit\Predicates\EqualsPredicate;
use DinoTech\LangKit\Predicates\GreaterThanPredicate;
use DinoTech\LangKit\Predicates\LeafAPredicate;
use DinoTech\LangKit\Predicates\LessThanPredicate;
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
