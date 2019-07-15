<?php
namespace DinoTech\Phelix\Expressions;

interface ContextInterface {
    public function lookupVar(string $varRef, $default = null);

    public function setVar(string $varRef, $value) : ContextInterface;
}
