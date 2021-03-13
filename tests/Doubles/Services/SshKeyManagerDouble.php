<?php

namespace App\Tests\Doubles\Services;

use App\Contracts\PersistenceManagerInterface;
use App\Services\SshKeyManager;

class SshKeyManagerDouble extends SshKeyManager
{
    /**
     * @var array|string[]
     */
    private array $files;

    /**
     * @param PersistenceManagerInterface $persistence
     * @param string $keysDirectory
     * @param array|string[] $files
     */
    public function __construct(
        PersistenceManagerInterface $persistence,
        string $keysDirectory,
        array $files
    )
    {
        parent::__construct($persistence, $keysDirectory);

        $this->files = $files;
    }

    /**
     * @return array|string[]
     */
    protected function getDirectoryFiles(): array
    {
        return $this->files;
    }
}