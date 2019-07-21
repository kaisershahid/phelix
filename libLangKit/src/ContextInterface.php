<?php
namespace DinoTech\LangKit;

/**
 * Provides a language context where you can get/set variables, constants, etc.
 */
interface ContextInterface {
    public function lookupVar(string $varRef, $default = null);

    public function setVar(string $varRef, $value) : ContextInterface;
}
