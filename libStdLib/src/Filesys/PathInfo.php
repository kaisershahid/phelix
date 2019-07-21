<?php
namespace DinoTech\StdLib\Filesys;

class PathInfo {
    private $info = ['dirname' => '', 'basename' => '', 'filename' => '', 'extension' => ''];

    public function __construct(string $file) {
        $this->info = array_replace($this->info, pathinfo($file));
    }

    public function dirname() : string {
        return $this->info['dirname'];
    }

    public function basename() : string {
        return $this->info['basename'];
    }

    public function filename() : string {
        return $this->info['filename'];
    }

    public function extension() : string {
        return $this->info['extension'];
    }

    public static function make($file) {
        return new self($file);
    }

    public function fullPath() : string {
        return $this->dirname() . '/' . $this->basename();
    }

    public function fullPathWithSuffix(string $suffix) : string {
        return $this->dirname() . '/' . $this->filename() . $suffix
            . ($this->extension() ? '.' . $this->extension() : '');
    }
}
