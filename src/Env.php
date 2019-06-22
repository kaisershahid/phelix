<?php
namespace DinoTech\Phelix;

/**
 * Helper for environment name check. Environment names are a mode plus optional
 * modifiers:
 *
 * - `dev`
 * - `prod.something`
 * - `stage.something.other`
 *
 * Modifiers allow more granular identification of a server so that it's possible
 * to have different clusters within the same mode. For instance, if there's a
 * shared dev environment, but the local environment varies in some way, you can
 * define both `dev` and `dev.local` configs (so that `dev.local` includes `dev`)
 */
class Env {
    private $name;
    private $mode;
    private $modifiers = [];

    public function __construct(string $name) {
        $this->name = $name;
        $parts = explode('.', $name);
        $this->mode = array_shift($parts);
        $this->modifiers = $parts;
        sort($this->modifiers);
    }

    public function getName() : string {
        return $this->name;
    }

    public function getMode() : string {
        return $this->mode;
    }

    public function getModifiers() : array {
        return $this->modifiers;
    }

    public function is(string $name) : bool {
        $parts = explode('.', $name, 2);
        $mode = array_shift($parts);
        $modifiers = array_shift($parts);

        return $this->isMode($mode) && $this->hasModifiers($modifiers);
    }

    public function isMode(string $mode) :bool {
        return $this->mode == $mode;
    }

    public function hasModifiers(string $modifiers = null) : bool {
        if (empty($modifiers)) {
            return true;
        }

        $mods = explode('.', $modifiers);
        sort($mods);
        return array_intersect($mods, $this->modifiers) == $mods;
    }
}
