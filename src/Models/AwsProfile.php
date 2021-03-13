<?php

namespace App\Models;

class AwsProfile
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
     * @var int
     */
    private int $usageCount;

    /**
     * @var string
     */
    private string $accessKeyId;

    /**
     * @var string
     */
    private string $secretAccessKey;

    /**
     * @var string|null
     */
    private ?string $region;

    /**
     * @param string $id
     * @param string $name
     * @param string $accessKeyId
     * @param string $secretAccessKey
     * @param string|null $region
     * @param int $usageCount
     */
    public function __construct(
        string $id,
        string $name,
        string $accessKeyId,
        string $secretAccessKey,
        ?string $region,
        int $usageCount
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->usageCount = $usageCount;
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->region = $region;
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
     * @return int
     */
    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    /**
     * @return string
     */
    public function getAccessKeyId(): string
    {
        return $this->accessKeyId;
    }

    /**
     * @return string
     */
    public function getSecretAccessKey(): string
    {
        return $this->secretAccessKey;
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }
}