<?php
namespace DinoTech\Phelix\Api\Config\Loaders;

use DinoTech\StdLib\Enum;
use DinoTech\StdLib\Filesys\Path;

/**
 * Universal YAML tag handlers for the framework.
 */
class StandardYamlCallbacks {
    const TAG_CONSTANTS = '!const';
    const TAG_RAW_FILE  = '!raw_file';

    protected $fileRoot;

    public function __construct() {
        $this->fileRoot = getcwd();
    }

    public function loadConstant($name, $tag, $flags) {
        if (defined($name)) {
            return constant($name);
        }

        throw new ParseException("$tag: $name doesn't exist");
    }

    public function loadRawFile($path, $tag, $flags) {
        // @todo define root
        $fullPath = Path::joinAndNormalize($this->fileRoot, $path);
        Path::checkPathUnderRoot($fullPath, $this->fileRoot);
        Path::checkFileExists($fullPath);

        return file_get_contents($fullPath);
    }

    // @todo loadConfigInline

    public function getCallbacks() : array {
        return [
            self::TAG_CONSTANTS => [$this, 'loadConstant'],
            //self::RAW_FILE => [$this, 'loadRawFile']
        ];
    }
}
