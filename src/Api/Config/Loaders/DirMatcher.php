<?php
namespace DinoTech\Phelix\Api\Config\Loaders;

class DirMatcher extends FileMatcher {
    public function getBase() : string {
        return $this->info->dirname() . '/' . $this->info->filename();
    }

    public function filterPath(string $path) : bool {
        return is_dir($path);
    }
}
