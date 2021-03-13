<?php

namespace App\Tests\Services;

use App\Services\AwsApiClient;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\TestCase;
use Aws\Credentials\Credentials;

class AwsApiClientTest extends TestCase
{
    /**
     * @var AwsApiClient
     */
    private AwsApiClient $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new AwsApiClient();
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(AwsApiClient::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_ec2_client(): void
    {
        $profile = AwsProfileFactory::create([
            'accessKeyId' => 'key',
            'secretAccessKey' => 'secret',
            'region' => 'eu-east-1',
        ]);

        $client = $this->subject->createEc2Client($profile);

        /** @var Credentials $credentials */
        $credentials = $client->getCredentials()->wait(true);

        self::assertSame('key', $credentials->getAccessKeyId());
        self::assertSame('secret', $credentials->getSecretKey());
        self::assertSame('eu-east-1', $client->getRegion());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_rds_client(): void
    {
        $profile = AwsProfileFactory::create([
            'accessKeyId' => 'key',
            'secretAccessKey' => 'secret',
            'region' => 'eu-east-1',
        ]);

        $client = $this->subject->createRdsClient($profile);

        /** @var Credentials $credentials */
        $credentials = $client->getCredentials()->wait(true);

        self::assertSame('key', $credentials->getAccessKeyId());
        self::assertSame('secret', $credentials->getSecretKey());
        self::assertSame('eu-east-1', $client->getRegion());
    }
}