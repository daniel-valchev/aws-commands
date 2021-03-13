<?php

namespace App\Utils;

use LogicException;

class FileUtils
{

    /**
     * Remove '.' and '..' path parts and make path absolute without
     * resolving symlinks.
     *
     * Examples:
     *
     *   resolvePath("test/./me/../now/", false);
     *   => test/now
     *
     *   resolvePath("test///.///me///../now/", true);
     *   => /home/example/test/now
     *
     *   resolvePath("test/./me/../now/", "/www/example.com");
     *   => /www/example.com/test/now
     *
     *   resolvePath("/test/./me/../now/", "/www/example.com");
     *   => /test/now
     *
     * @access public
     * @param string $path
     * @param mixed $basePath resolve paths realtively to this path. Params:
     *                        STRING: prefix with this path;
     *                        TRUE: use current dir;
     *                        FALSE: keep relative (default)
     * @return string resolved path
     */
    public static function resolvePath($path, $basePath = false): string
    {
        if ($path[0] === '~') {
            $path = getenv('HOME') . substr($path, 1);
        }

        // Make absolute path
        if ($path[0] !== DIRECTORY_SEPARATOR) {
            if ($basePath === true) {
                // Get PWD first to avoid getcwd() resolving symlinks if in symlinked folder
                $path = (getenv('PWD') ?: getcwd()) . DIRECTORY_SEPARATOR . $path;
            } elseif (strlen($basePath)) {
                $path = $basePath . DIRECTORY_SEPARATOR . $path;
            }
        }

        // Resolve '.' and '..'
        $components = [];
        foreach (explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR)) as $name) {
            if ($name === '..') {
                array_pop($components);
            } elseif ($name !== '.' && !(count($components) && $name === '')) {
                // … && !(count($components) && $name === '') - we want to keep initial '/' for abs paths
                $components[] = $name;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $components);
    }

}