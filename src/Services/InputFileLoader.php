<?php

namespace App\Services;

use App\Exceptions\AbortCommandException;
use App\Exceptions\InputFileNotReadable;
use Dotenv\Dotenv;

class InputFileLoader
{
    /**
     * @var string
     */
    private string $inputFileName;

    /**
     * @param string $inputFileName
     */
    public function __construct(string $inputFileName)
    {
        $this->inputFileName = $inputFileName;
    }

    /**
     * @return array
     * @throws AbortCommandException
     */
    public function getInput(): array
    {
        if ($file = $this->findFile()) {
            if (!is_readable($file)) {
                throw new InputFileNotReadable(
                    sprintf('File "%s" is not readable.', $file)
                );
            }

            try {
                return Dotenv::parse(file_get_contents($file));
            }
            catch (\Throwable $e) {
                throw new AbortCommandException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return [];
    }

    /**
     * @return string|null
     */
    protected function findFile(): ?string
    {
        $cwd = getcwd();
        $file = $cwd . DIRECTORY_SEPARATOR . $this->inputFileName;

        while ((!file_exists($file) || is_dir($file)) && $cwd !== '/') {
            $cwd = dirname($cwd);
            $file = str_replace(
                DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                $cwd . DIRECTORY_SEPARATOR . $this->inputFileName
            );
        }

        return file_exists($file) && !is_dir($file)
            ? $file
            : null;
    }

}