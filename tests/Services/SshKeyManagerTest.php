<?php

namespace App\Tests\Services;

use App\Contracts\PersistenceManagerInterface;
use App\Services\SshKeyManager;
use App\Tests\Doubles\Services\SshKeyManagerDouble;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\Factories\SshKeyFactory;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SshKeyManagerTest extends TestCase
{

    /**
     * @var PersistenceManagerInterface|MockObject
     */
    private PersistenceManagerInterface $persistence;

    /**
     * @var SshKeyManager
     */
    private SshKeyManager $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->persistence = $this->createMock(PersistenceManagerInterface::class);
        $this->subject = new SshKeyManager(
            $this->persistence,
            '~/.ssh'
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_available_key_files(): void
    {
        $files = [
            '.',
            '..',
            'config',
            '.DS_Store',
            'known_hosts',
            'test.pub',
            'test.pem',
            'ssh_key',
            'ssh_key2',
            'ssh_key3.id_rsa',
        ];

        $subject = new SshKeyManagerDouble(
            $this->persistence,
            '~/.ssh',
            $files
        );

        $keys = $subject->getAvailableKeys();

        self::assertCount(3, $keys);

        $expected = [
            'ssh_key',
            'ssh_key2',
            'ssh_key3.id_rsa',
        ];

        foreach ($keys as $index => $value) {
            self::assertSame($expected[$index], $value->getName());
            self::assertSame('~/.ssh/' . $expected[$index], $value->getFullPath());
        }
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_last_used_key_by_profile(): void
    {
        $profile = AwsProfileFactory::create();

        $this->persistence->method('get')
            ->with('last_used_keys')
            ->willReturn([
                $profile->getId() => '/tmp/ssh_key',
            ]);

        $key = $this->subject->getLastUsedKeyByProfile($profile);

        self::assertNotNull($key);
        self::assertSame('/tmp/ssh_key', $key->getFullPath());
        self::assertSame('ssh_key', $key->getName());
    }

    /**
     * @test
     * @return void
     */
    public function it_sets_last_used_key_by_profile(): void
    {
        $profile = AwsProfileFactory::create();
        $key = SshKeyFactory::create();

        $this->persistence->expects(self::once())
            ->method('set')
            ->with('last_used_keys');

        $this->subject->setLastUsedKey($profile, $key);
    }

}