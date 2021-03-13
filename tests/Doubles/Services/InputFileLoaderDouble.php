<?php

namespace App\Tests\Doubles\Services;

use App\Services\InputFileLoader;

class InputFileLoaderDouble extends InputFileLoader
{
    /**
     * @var string
     */
    private string $filepath;

    /**
     * @param string $filepath
     */
    public function __construct(
        string $filepath
    )
    {
        $this->filepath = $filepath;
        $inputFileName = pathinfo($filepath, PATHINFO_FILENAME);

        parent::__construct($inputFileName);
    }

    /**
     * @return string|null
     */
    protected function findFile(): ?string
    {
        return $this->filepath;
    }
}