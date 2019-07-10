<?php
namespace DinoTech\Phelix\Api\Config\Loaders;

use DinoTech\StdLib\Filesys\Path;
use DinoTech\StdLib\Filesys\PathInfo;
use DinoTech\StdLib\KeyValue;

/**
 * Given a filename `a.ext`, finds all files matching `a.*.ext`. The term _suffix_
 * applies to the portion matched in `*`.
 */
class FileMatcher {
    protected $root;
    protected $base;
    protected $info = [];

    public function __construct(string $baseFile, string $root = null) {
        $this->root = $root ?: getcwd();
        $this->base = $baseFile;
        $this->info = PathInfo::make(Path::join($this->root, $this->base));
    }

    /**
     * Finds matching files and returns the suffixes.
     *
     * ```
     * abcdef.******.extension
     * [start suffix [end    ]
     * ```
     * @return array
     */
    public function getMatchingSuffixes() : array {
        $base = $this->getBase();
        $ext = $this->info->extension();
        $remStart = strlen($base);
        $remEnd = 0 - strlen($ext) - 1;
        // means no extension
        if ($remEnd == -1) {
            $remEnd = 0;
        }

        $pattern = $this->info->dirname() . '/' . $this->getNamePattern();
        $filtered = array_filter(glob($pattern), [$this, 'filterPath']);
        return
            array_map(function(string $filePath) use ($remStart, $remEnd) {
                return substr($filePath, $remStart, $remEnd);
            }, $filtered);
    }

    public function getBase() : string {
        return $this->info->dirname() . '/' . $this->info->filename() . '.';
    }

    /**
     * Determines whether to use or discard path.
     * @param string $path The absolute path to check
     * @return bool
     */
    public function filterPath(string $path) : bool {
        return is_file($path);
    }

    public function getNamePattern() {
        $pattern = $this->info->filename() . '.*';
        if ($this->info->extension()) {
            $pattern .= '.' . $this->info->extension();
        }

        return $pattern;
    }

    /**
     * Returns `dirname + '/' + filename + '.' + suffix + '.' . extension`
     * @param string $suffix excludes leading '.'
     * @return string
     */
    public function getFullPathBySuffix(string $suffix) : string {
        return $this->info->fullPathWithSuffix('.' . $suffix);
    }
}
