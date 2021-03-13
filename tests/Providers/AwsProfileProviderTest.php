<?php

namespace App\Tests\Providers;

use App\Exceptions\MissingAwsProfileCredentialsException;
use App\Models\AwsProfile;
use App\Providers\AwsProfileProvider;
use App\Services\AwsProfileManager;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AwsProfileProviderTest extends TestCase
{
    /**
     * @var AwsProfileManager|MockObject
     */
    private AwsProfileManager $awsProfileManager;

    /**
     * @var SymfonyStyle|MockObject
     */
    private SymfonyStyle $io;

    /**
     * @var InputInterface|MockObject
     */
    private InputInterface $input;

    /**
     * @var AwsProfileProvider
     */
    private AwsProfileProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->awsProfileManager = $this->createMock(AwsProfileManager::class);
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->subject = new AwsProfileProvider(
            $this->awsProfileManager,
            $this->io,
            $this->input
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(AwsProfileProvider::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_arguments(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects(self::once())
            ->method('addArgument')
            ->with('AWS profile');

        AwsProfileProvider::setup($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_options(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects(self::once())
            ->method('addOption')
            ->with('select-aws-profile');

        AwsProfileProvider::setup($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_reports_error_if_no_aws_profiles_are_detected(): void
    {
        $this->input->method('getArgument')->willReturn(null);

        $this->expectException(MissingAwsProfileCredentialsException::class);

        $this->subject->getAwsProfile();
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_profile_if_no_argument_is_passed(): void
    {
        $profiles = [
            AwsProfileFactory::create([
                'name' => 'Profile 1',
            ]),
            AwsProfileFactory::create([
                'name' => 'Profile 2',
            ])
        ];

        $this->awsProfileManager->method('getProfiles')
            ->willReturn($profiles);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select AWS profile',
                [
                    'Profile 1',
                    'Profile 2',
                ]
            )
            ->willReturn('Profile 1');

        self::assertSame($profiles[0], $this->subject->getAwsProfile());
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_profile_if_argument_is_passed(): void
    {
        $profile = $this->createMock(AwsProfile::class);

        $this->input->method('getArgument')->willReturn('test');
        $this->awsProfileManager->method('findProfile')
            ->with('test')
            ->willReturn(
                $profile
            );

        self::assertSame($profile, $this->subject->getAwsProfile());
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_profile_if_missing_profile_is_passed(): void
    {
        $this->input->method('getArgument')->willReturn('test');
        $this->awsProfileManager->method('findProfile')
            ->with('test')
            ->willReturn(null);

        $profiles = [
            AwsProfileFactory::create([
                'name' => 'Profile 1',
            ]),
            AwsProfileFactory::create([
                'name' => 'Profile 2',
            ])
        ];

        $this->awsProfileManager->method('getProfiles')
            ->willReturn($profiles);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select AWS profile',
                [
                    'Profile 1',
                    'Profile 2',
                ]
            )
            ->willReturn('Profile 1');

        self::assertSame($profiles[0], $this->subject->getAwsProfile());
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_last_used_profile_if_available(): void
    {
        $lastProfile = AwsProfileFactory::create();

        $this->awsProfileManager->method('getLastUsedProfile')
            ->willReturn($lastProfile);

        self::assertSame($lastProfile, $this->subject->getAwsProfile());
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_profile_if_last_used_profile_is_available_and_enforced_from_option(): void
    {
        $lastProfile = AwsProfileFactory::create([
            'Name' => 'Last Profile',
        ]);

        $profiles = [
            AwsProfileFactory::create([
                'name' => 'Profile 1',
            ]),
            AwsProfileFactory::create([
                'name' => 'Profile 2',
            ])
        ];

        $this->awsProfileManager->method('getLastUsedProfile')
            ->willReturn($lastProfile);

        $this->input->method('getOption')
            ->with('select-aws-profile')
            ->willReturn(true);

        $this->awsProfileManager->method('getProfiles')
            ->willReturn($profiles);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select AWS profile',
                [
                    'Profile 1',
                    'Profile 2',
                ]
            )
            ->willReturn('Profile 1');

        self::assertSame($profiles[0], $this->subject->getAwsProfile());
    }

    /**
     * @test
     * @return void
     */
    public function it_stores_last_used_profile(): void
    {
        $profile = AwsProfileFactory::create();
        $this->input->method('getArgument')->willReturn('test');
        $this->awsProfileManager->method('findProfile')
            ->with('test')
            ->willReturn($profile);

        $this->awsProfileManager->expects(self::once())
            ->method('setLastUsedProfile')
            ->with($profile);

        $this->subject->getAwsProfile();
    }

    /**
     * @test
     * @return void
     */
    public function it_increments_profile_usages(): void
    {
        $profiles = [
            AwsProfileFactory::create([
                'name' => 'Profile 1',
            ]),
            AwsProfileFactory::create([
                'name' => 'Profile 2',
            ])
        ];

        $this->awsProfileManager->method('getProfiles')
            ->willReturn($profiles);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select AWS profile',
                [
                    'Profile 1',
                    'Profile 2',
                ]
            )
            ->willReturn('Profile 1');

        $this->awsProfileManager->expects(self::once())
            ->method('incrementUsages')
            ->with($profiles[0]);

        self::assertSame($profiles[0], $this->subject->getAwsProfile());
    }

}