<?php
namespace DinoTech\Phelix\Api\Config;

class GenericConfig extends ConfigLoader {
    private $noCallbacks = false;

    public function noCallbacks() : GenericConfig {
        $this->noCallbacks = true;
        return $this;
    }

    protected function getCallbacks(): array {
        return $this->noCallbacks ? [] : (new StandardYamlCallbacks())->getCallbacks();
    }
}
