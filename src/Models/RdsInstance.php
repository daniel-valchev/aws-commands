<?php

namespace App\Models;

class RdsInstance
{

    /**
     * @var string
     */
    private string $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $port;

    /**
     * @var string
     */
    private string $instanceClass;

    /**
     * @param string $id
     * @param string $name
     * @param string $host
     * @param int $port
     * @param string $instanceClass
     */
    public function __construct(
        string $id,
        string $name,
        string $host,
        int $port,
        string $instanceClass
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->host = $host;
        $this->port = $port;
        $this->instanceClass = $instanceClass;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->name} [{$this->instanceClass} {$this->host}:{$this->port}]";
    }

    /**
     * @return string
     */
    public function getInstanceClass(): string
    {
        return $this->instanceClass;
    }

    /**
     * @param RdsInstance $object
     * @return bool
     */
    public function equals(RdsInstance $object): bool
    {
        return $this->getId() === $object->getId() &&
            $this->getHost() === $object->getHost() &&
            $this->getName() === $object->getName() &&
            $this->getInstanceClass() === $object->getInstanceClass() &&
            $this->getPort() === $object->getPort();
    }

}