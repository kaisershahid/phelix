<?php
namespace DinoTech\Phelix\Api\Service\Registry;

use DinoTech\Phelix\Api\Service\ReferenceCardinality;
use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Enum;

/**
 * Increase or decrease points assigned to a key. Primarily used to track dependency
 * resolution:
 *
 * ```php
 * $score = Scoreboard::makeFromCollection(ReferenceCardinality::values());
 * $score->increase(ReferenceCardinality::ONE());
 * ```
 *
 * @package DinoTech\Phelix\Api\Service\Registry
 */
class Scoreboard implements \JsonSerializable {
    private $scores = [];

    public function __construct(array $categories) {
        foreach ($categories as $cat) {
            $this->scores[$cat] = 0;
        }
    }

    public function getTotalScore() : int {
        return array_sum($this->scores);
    }

    public function getScore(string $category) : int {
        return $this->scores[$category];
    }

    public function increase(string $category, $by = 1) : Scoreboard {
        $this->scores[$category] += $by;
        return $this;
    }

    public function decrease(string $category, $by = 1) : Scoreboard {
        $this->scores[$category] -= $by;
        return $this;
    }

    public function jsonSerialize() {
        return $this->scores;
    }

    public static function makeFromCollection(Collection $collection) : Scoreboard {
        return new self($collection->keys());
    }

    public static function makeForCardinality() {
        return new self(ReferenceCardinality::values()->keys());
    }
}
