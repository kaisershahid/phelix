<?php
namespace DinoTech\Phelix\Expressions;

use DinoTech\LangKit\ContextInterface;
use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\StandardMap;

class BasicContext implements ContextInterface {
    protected $ctx;

    public function __construct(array $ctx) {
        $this->ctx = new StandardMap($ctx);
    }

    public function lookupVar(string $ref, $default = null) {
        return ArrayUtils::getNested($this->ctx, $ref, '.', $default);
    }

    public function setVar(string $varRef, $value): ContextInterface {
        // @todo
        return $this;
    }
}
