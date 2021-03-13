<?php

namespace App\Tests\Factories;

class TempFileFactory
{

    /**
     * @param string|null $contents
     * @return string
     */
    public static function create(?string $contents = null): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'aws-commands');

        if ($tempFile === false) {
            throw new \RuntimeException('Unable to create temp file.');
        }

        if (count(func_get_args())) {
            file_put_contents($tempFile, $contents);
        }

        return $tempFile;
    }

}