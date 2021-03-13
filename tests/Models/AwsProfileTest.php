<?php

namespace App\Tests\Models;

use App\Models\AwsProfile;
use App\Tests\TestCase;

class AwsProfileTest extends TestCase
{
    /**
     * @var AwsProfile
     */
    private AwsProfile $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new AwsProfile(
            'id',
            'name',
            'access_key_id',
            'secret_access_key',
            'region',
            1
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(AwsProfile::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_name(): void
    {
        self::assertSame('name', $this->subject->getName());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_id(): void
    {
        self::assertSame('id', $this->subject->getId());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_usage_count(): void
    {
        self::assertSame(1, $this->subject->getUsageCount());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_access_key_id(): void
    {
        self::assertSame('access_key_id', $this->subject->getAccessKeyId());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_secret_access_key(): void
    {
        self::assertSame('secret_access_key', $this->subject->getSecretAccessKey());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_region(): void
    {
        self::assertSame('region', $this->subject->getRegion());
    }
}