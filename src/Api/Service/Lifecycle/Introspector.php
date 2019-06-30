<?php
namespace DinoTech\Phelix\Api\Service\Lifecycle;

class Introspector {
    private $component;
    /** @var \ReflectionClass */
    private $reflection;

    public function __construct($component) {
        $this->component = $component;
        $this->reflection = new \ReflectionClass($component);
    }

    public function invokeMethod($name, ...$args) {
        $meth = $this->reflection->getMethod($name);
        $meth->setAccessible(true);
        return $meth->invokeArgs($this->component, $args);
    }
}
