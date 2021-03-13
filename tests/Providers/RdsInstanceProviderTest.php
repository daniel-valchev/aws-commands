<?php

namespace App\Tests\Providers;

use App\Exceptions\NoRunningRdsInstancesFoundException;
use App\Models\RdsInstance;
use App\Providers\RdsInstanceProvider;
use App\Services\RdsInstanceManager;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\Factories\RdsInstanceFactory;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Style\SymfonyStyle;

class RdsInstanceProviderTest extends TestCase
{

    /**
     * @var RdsInstanceManager|MockObject
     */
    private RdsInstanceManager $rdsInstanceManager;

    /**
     * @var SymfonyStyle|MockObject
     */
    private SymfonyStyle $io;

    /**
     * @var RdsInstanceProvider
     */
    private RdsInstanceProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rdsInstanceManager = $this->createMock(RdsInstanceManager::class);
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->subject = new RdsInstanceProvider(
            $this->rdsInstanceManager,
            $this->io
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(RdsInstanceProvider::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_ec2_instance(): void
    {
        $profile = AwsProfileFactory::create();

        /** @var RdsInstance[] $instances */
        $instances = [
            RdsInstanceFactory::create([
                'name' => 'rds-1',
            ]),
            RdsInstanceFactory::create([
                'name' => 'rds-2',
            ]),
        ];

        $this->rdsInstanceManager->expects(self::once())
            ->method('getRdsInstances')
            ->with($profile)
            ->willReturn($instances);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select RDS instance',
                [
                    (string) $instances[0],
                    (string) $instances[1],
                ]
            )
            ->willReturn((string) $instances[0]);

        self::assertSame($instances[0], $this->subject->getRdsInstance($profile));
    }

    /**
     * @test
     * @return void
     */
    public function it_reports_error_if_no_running_rds_instances_are_found(): void
    {
        $profile = AwsProfileFactory::create();

        $this->rdsInstanceManager->expects(self::once())
            ->method('getRdsInstances')
            ->with($profile)
            ->willReturn([]);

        $this->expectException(NoRunningRdsInstancesFoundException::class);

        $this->subject->getRdsInstance($profile);
    }

}