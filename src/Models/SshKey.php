<?php

namespace App\Models;

class SshKey
{

    /**
     * @var string
     */
    private string $fullPath;

    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $fullPath
     * @param string $name
     */
    public function __construct(string $fullPath, string $name)
    {
        $this->fullPath = $fullPath;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @param SshKey $object
     * @return bool
     */
    public function equals(SshKey $object): bool
    {
        return $this->getName() === $object->getName() &&
            $this->getFullPath() === $object->getFullPath();
    }

}