<?php

namespace App\Tests\Services;

use App\Contracts\PersistenceManagerInterface;
use App\Services\AwsProfileManager;
use App\Tests\Factories\TempFileFactory;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AwsProfileManagerTest extends TestCase
{

    /**
     * @var PersistenceManagerInterface|MockObject
     */
    private PersistenceManagerInterface $persistence;

    /**
     * @var AwsProfileManager
     */
    private AwsProfileManager $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $credentials = <<<CONTENT
        [profile1]
        aws_access_key_id = key_id_1
        aws_secret_access_key = access_key_1
        
        [profile2]
        aws_access_key_id = key_id_2
        aws_secret_access_key = access_key_2
        CONTENT;

        $configuration = <<<CONTENT
        [profile profile1]
        region = eu-west-2
        
        [profile profile2]
        region = eu-east-1
        CONTENT;

        $profileCredentialsPath = TempFileFactory::create($credentials);
        $profileConfigurationPath = TempFileFactory::create($configuration);

        $this->persistence = $this->createMock(PersistenceManagerInterface::class);
        $this->subject = new AwsProfileManager(
            $this->persistence,
            $profileCredentialsPath,
            $profileConfigurationPath
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(AwsProfileManager::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_available_profiles(): void
    {
        $profiles = $this->subject->getProfiles();

        self::assertCount(2, $profiles);

        self::assertSame('profile1', $profiles[0]->getName());
        self::assertSame('profile1', $profiles[0]->getId());
        self::assertSame('key_id_1', $profiles[0]->getAccessKeyId());
        self::assertSame('access_key_1', $profiles[0]->getSecretAccessKey());
        self::assertSame('eu-west-2', $profiles[0]->getRegion());

        self::assertSame('profile2', $profiles[1]->getName());
        self::assertSame('profile2', $profiles[1]->getId());
        self::assertSame('key_id_2', $profiles[1]->getAccessKeyId());
        self::assertSame('access_key_2', $profiles[1]->getSecretAccessKey());
        self::assertSame('eu-east-1', $profiles[1]->getRegion());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_empty_array_if_credentials_file_does_not_exists(): void
    {
        $subject = new AwsProfileManager(
            $this->persistence,
            '/tmp/nonexisting',
            '/tmp/nonexisting'
        );

        $profiles = $subject->getProfiles();
        self::assertEmpty($profiles);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_profile_by_id(): void
    {
        self::assertNotNull($this->subject->findProfile('profile1'));
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_null_if_profile_is_not_existing(): void
    {
        self::assertNull($this->subject->findProfile('non_existing_profile'));
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_last_used_profile(): void
    {
        $this->persistence->method('get')
            ->willReturnCallback(function($key) {
                if ($key === 'last_used_profile') {
                    return 'profile1';
                }

                return null;
            });

        $lastUsedProfile = $this->subject->getLastUsedProfile();

        self::assertNotNull($lastUsedProfile);
        self::assertSame('profile1', $lastUsedProfile->getName());
        self::assertSame('profile1', $lastUsedProfile->getId());
        self::assertSame('key_id_1', $lastUsedProfile->getAccessKeyId());
        self::assertSame('access_key_1', $lastUsedProfile->getSecretAccessKey());
        self::assertSame('eu-west-2', $lastUsedProfile->getRegion());
    }


    /**
     * @test
     * @return void
     */
    public function it_sets_last_used_profile(): void
    {
        $this->persistence->expects(self::once())
            ->method('set')
            ->with('last_used_profile', 'profile1');

        $profiles = $this->subject->getProfiles();

        $this->subject->setLastUsedProfile($profiles[0]);
    }

    /**
     * @test
     * @return void
     */
    public function it_increments_usages(): void
    {
        $profiles = $this->subject->getProfiles();

        self::assertSame(0, $profiles[0]->getUsageCount());

        $this->persistence->expects(self::once())
            ->method('set')
            ->with('profile_usages');

        $this->subject->incrementUsages($profiles[0]);

        self::assertSame(0, $profiles[1]->getUsageCount());
    }

}