<?php

namespace App\Tests\Services;

use App\Models\RdsInstance;
use App\Services\AwsApiClient;
use App\Services\RdsInstanceManager;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\TestCase;
use Aws\MockHandler;
use Aws\Rds\RdsClient;
use Aws\Result;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;

class RdsInstanceManagerTest extends TestCase
{

    /**
     * @var AwsApiClient|MockObject
     */
    private AwsApiClient $awsApiClient;

    /**
     * @var RdsInstanceManager
     */
    private RdsInstanceManager $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->awsApiClient = $this->createMock(AwsApiClient::class);
        $this->subject = new RdsInstanceManager(
            $this->awsApiClient
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(RdsInstanceManager::class, $this->subject);
    }

    /**
     * @test
     * @return void
     * @throws JsonException
     */
    public function it_returns_rds_instances(): void
    {
        $profile = AwsProfileFactory::create();
        $root = $this->container->get('root_folder');

        $response = json_decode(
            file_get_contents("$root/tests/data/rds_instances.json"),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $existingInstances = [
            new RdsInstance(
                'terraform-20191003130158387600000001',
                'Production Test',
                'terraform-20191003130158387600000001.cqdfx0yssnjm.eu-west-2.rds.amazonaws.com',
                5432,
                'db.t3.small',
            ),
            new RdsInstance(
                'terraform-20190627132842861700000006',
                'UAT Test',
                'terraform-20190627132842861700000006.cqdfx0yssnjm.eu-west-2.rds.amazonaws.com',
                5432,
                'db.t3.small',
            ),
        ];

        $mock = new MockHandler();
        $mock->append(new Result($response));

        $rdsClient = new RdsClient([
            'region'  => 'eu-west-2',
            'version' => 'latest',
            'handler' => $mock,
            'credentials' => [
                'key'    => 'test',
                'secret' => 'test',
            ],
        ]);

        $this->awsApiClient->method('createRdsClient')
            ->willReturn($rdsClient);

        $instances = $this->subject->getRdsInstances($profile);

        self::assertCount(count($existingInstances), $instances);

        foreach ($instances as $key => $instance) {
            self::assertTrue($existingInstances[$key]->equals($instance));
        }
    }

}