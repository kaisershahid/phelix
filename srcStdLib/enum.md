# Enums

Our take on enums is that enums:

1. are well-ordered;
2. are identified and referenced by name;
3. can represent complex values.

By _well ordered_, we should be able to compare enums of the same type. In this way, defining something like a set of priorities will ensure that the first defined priority is the lowest while the last priority is the highest.

By _identified and referenced by name_, we should be able to pass and store an enum's idenity only by name. If the set of priorities above only define a number (let's say 0 to 10), that value loses context immediately when stored in a database. In every case, the name of an enum provides far more context than its value.

By _represent complex values_, enums shouldn't be limited to a scalar value. They should be able to represent any static data.

Because of this, we have developed our own `Enum` instead of using Consistence's.

## Example

```php
use DinoTech\Phelix\StdLib\Enum;

class BabyWeight extends Enum {
    const LOW = ['min' => 1, 'max' => 5.999];
    const MEDIUM = ['min' => 6, 'max' => 9.999];
    const HIGH = ['min' => 10, 'max' => 15];
    
    public function isWeightInRange($weight) {
        $value = $this->value();
        return $value['min'] <= $weight && $value['max'] >= $weight;
    }
}

$baby1 = BabyWeight::LOW();
$baby2 = BabyWeight::HIGH();
$babies = [$baby1, $baby2];
shuffle($babies);
if ($babies[0].rankedHigherThan($babies[1])) {
    echo "{$babies[0]->name()} weights more than {$babies[1]->name()}";
} else {
    echo "{$babies[0]->name()} weights less than {$babies[1]->name()}";
}

BabyWeight::values()->traverse(function(KeyValue $kv) {
    $enum = $kv->value();
    if ($enum->isInRange(10.9)) {
        echo "10.9 is considered {$enum->name()}";
    }
});
```
