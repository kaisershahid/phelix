<?php
namespace DinoTech\Phelix\Api\Service\Query;

class SimpleQueryParser {
    private $raw;
    private $preds;

    public function __construct($query) {
        $this->raw = $query;
        $this->preds = [];
        $parts = preg_split('#\s*&&\s*#', $query);
        foreach ($parts as $pred) {
            $propOpConstraint = preg_split('#\s*=\s*', $pred, 2);
            $prop = array_shift($propOpConstraint);
            $cons = array_shift($propOpConstraint);
            $this->preds[$prop] = $cons;
        }
    }

    /**
     * @return array
     */
    public function getPredicates() {
        return $this->preds;
    }
}
