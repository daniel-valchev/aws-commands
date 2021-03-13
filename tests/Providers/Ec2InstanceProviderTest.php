<?php

namespace App\Tests\Providers;

use App\Exceptions\NoRunningEc2InstancesFoundException;
use App\Models\EC2Instance;
use App\Providers\EC2InstanceProvider;
use App\Services\EC2InstanceManager;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\Factories\Ec2InstanceFactory;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Style\SymfonyStyle;

class Ec2InstanceProviderTest extends TestCase
{
    /**
     * @var EC2InstanceManager|MockObject
     */
    private EC2InstanceManager $ec2InstanceManager;

    /**
     * @var SymfonyStyle|MockObject
     */
    private SymfonyStyle $io;

    /**
     * @var EC2InstanceProvider
     */
    private EC2InstanceProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ec2InstanceManager = $this->createMock(EC2InstanceManager::class);
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->subject = new EC2InstanceProvider(
            $this->ec2InstanceManager,
            $this->io
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(EC2InstanceProvider::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_ec2_instance(): void
    {
        $profile = AwsProfileFactory::create();

        /** @var EC2Instance[] $instances */
        $instances = [
            Ec2InstanceFactory::create([
                'name' => 'ec2-1',
            ]),
            Ec2InstanceFactory::create([
                'name' => 'ec2-2',
            ]),
        ];

        $this->ec2InstanceManager->expects(self::once())
            ->method('getRunningInstances')
            ->with($profile)
            ->willReturn($instances);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select EC2 instance',
                [
                    (string) $instances[0],
                    (string) $instances[1],
                ]
            )
            ->willReturn((string) $instances[0]);

        self::assertSame($instances[0], $this->subject->getEC2Instance($profile));
    }

    /**
     * @test
     * @return void
     */
    public function it_reports_error_if_no_running_ec2_instances_are_found(): void
    {
        $profile = AwsProfileFactory::create();

        $this->ec2InstanceManager->expects(self::once())
            ->method('getRunningInstances')
            ->with($profile)
            ->willReturn([]);

        $this->expectException(NoRunningEc2InstancesFoundException::class);

        $this->subject->getEC2Instance($profile);
    }

}