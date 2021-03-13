<?php

namespace App\Tests\Services;

use App\Models\EC2Instance;
use App\Services\AwsApiClient;
use App\Services\EC2InstanceManager;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\TestCase;
use Aws\Ec2\Ec2Client;
use Aws\MockHandler;
use Aws\Result;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;

class EC2InstanceManagerTest extends TestCase
{

    /**
     * @var AwsApiClient|MockObject
     */
    private AwsApiClient $awsApiClient;

    /**
     * @var EC2InstanceManager
     */
    private EC2InstanceManager $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->awsApiClient = $this->createMock(AwsApiClient::class);
        $this->subject = new EC2InstanceManager(
            $this->awsApiClient
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(EC2InstanceManager::class, $this->subject);
    }

    /**
     * @test
     * @return void
     * @throws JsonException
     */
    public function it_returns_ec2_running_instances(): void
    {
        $profile = AwsProfileFactory::create();
        $root = $this->container->get('root_folder');

        $response = json_decode(
            file_get_contents("$root/tests/data/ec2_instances.json"),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $existingInstances = [
            new EC2Instance(
                'test-prod',
                '3.10.52.196',
                'ec2-3-10-52-196.eu-west-2.compute.amazonaws.com'
            ),
            new EC2Instance(
                'test-prod',
                '18.132.41.193',
                'ec2-18-132-41-193.eu-west-2.compute.amazonaws.com'
            ),
            new EC2Instance(
                'test-prod-worker',
                '35.176.39.75',
                'ec2-35-176-39-75.eu-west-2.compute.amazonaws.com'
            ),
            new EC2Instance(
                'test-uat',
                '35.177.198.126',
                'ec2-35-177-198-126.eu-west-2.compute.amazonaws.com'
            ),
            new EC2Instance(
                'test-uat-worker',
                '3.8.48.157',
                'ec2-3-8-48-157.eu-west-2.compute.amazonaws.com'
            ),
        ];

        $mock = new MockHandler();
        $mock->append(new Result($response));

        $ec2Client = new Ec2Client([
            'region'  => 'eu-west-2',
            'version' => 'latest',
            'handler' => $mock,
            'credentials' => [
                'key'    => 'test',
                'secret' => 'test',
            ],
        ]);

        $this->awsApiClient->method('createEc2Client')
            ->willReturn($ec2Client);

        $instances = $this->subject->getRunningInstances($profile);

        self::assertCount(count($existingInstances), $instances);

        foreach ($instances as $key => $instance) {
            self::assertTrue($existingInstances[$key]->equals($instance));
        }
    }

}