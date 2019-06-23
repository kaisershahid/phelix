<?php
namespace DinoTech\StdLib\Filesys;

/**
 * Various path-related functions. For any explicit root parameters, the default
 * is to use the current working directory via `getcwd()`.
 */
class Path {
    /**
     * Turns backslashes to forward slashes.
     * @param string $str
     * @return string
     */
    public static function fixSlashes(string $str) : string {
        return str_replace('\\', '/', $str);
    }
    /**
     * Joins multiple path parts together, ensuring no duplicated '/' separators.
     * Any backslash is converted to forward slash.
     * @param string ...$pathParts
     * @return string
     */
    public static function join(string ...$pathParts) : string {
        $parts = array_map([self::class, 'fixSlashes'], $pathParts);
        $buff = [self::chompRightSlash(array_shift($parts))];
        foreach ($parts as $part) {
            $buff[] = self::chompLeftSlash($part);
        }

        return implode('/', $buff);
    }

    public static function chompLeftSlash(string $str) : string {
        while ($str[0] == '/') {
            $str = substr($str, 1);
        }

        return $str;
    }

    public static function chompRightSlash(string $str) : string {
        while ($str[-1] == '/') {
            $str = substr($str, 0, -1);
        }

        return $str;
    }

    /**
     * @param string $path
     * @param string $root
     * @throws PathException
     */
    public static function checkPathUnderRoot(string $path, string $root) {
        $realpath = realpath($path);
        $realroot = self::chompRightSlash($root) . '/';
        if (!strpos($realroot, $realpath) === 0) {
            throw new PathException("$realpath not under $root");
        }
    }

    /**
     * @param string $path
     * @throws PathException
     */
    public static function checkFileExists(string $path) {
        if (!file_exists($path)) {
            throw new PathException("$path does not exist");
        }
    }
}
