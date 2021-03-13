<?php

namespace App\Models;

class EC2Instance
{

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $ip;

    /**
     * @var string
     */
    private string $dns;

    /**
     * @param string $name
     * @param string $ip
     * @param string $dns
     */
    public function __construct(string $name, string $ip, string $dns)
    {
        $this->name = $name;
        $this->ip = $ip;
        $this->dns = $dns;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->name} [{$this->ip}]";
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
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getDns(): string
    {
        return $this->dns;
    }

    /**
     * @param EC2Instance $object
     * @return bool
     */
    public function equals(EC2Instance $object): bool
    {
        return $this->getIp() === $object->getIp() &&
            $this->getName() === $object->getName() &&
            $this->getDns() === $object->getDns();
    }

}