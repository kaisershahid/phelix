<?php
namespace DinoTech\Phelix\Api\Config\Loaders;

abstract class ConfigLoader {
    public function loadYamlFromFile($path) {
        if (!file_exists($path)) {
            throw new \RuntimeException("can't load config $path"); // @todo custom exception
        }

        $ndocs = null;
        return yaml_parse_file($path, 0, $ndocs, $this->getCallbacks());
    }

    abstract protected function getCallbacks() : array;
}
