<?php

namespace App\Tests\Providers;

use App\Providers\SshKeyProvider;
use App\Services\SshKeyManager;
use App\Tests\Factories\AwsProfileFactory;
use App\Tests\Factories\SshKeyFactory;
use App\Tests\Factories\TempFileFactory;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SshKeyProviderTest extends TestCase
{
    /**
     * @var SymfonyStyle|MockObject
     */
    private SymfonyStyle $io;

    /**
     * @var InputInterface|MockObject
     */
    private InputInterface $input;

    /**
     * @var SshKeyManager|MockObject
     */
    private SshKeyManager $sshKeyManager;

    /**
     * @var SshKeyProvider
     */
    private SshKeyProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->sshKeyManager = $this->createMock(SshKeyManager::class);
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->subject = new SshKeyProvider(
            $this->sshKeyManager,
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
        self::assertInstanceOf(SshKeyProvider::class, $this->subject);
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
            ->with('SSH key');

        SshKeyProvider::setup($command);
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
            ->with('select-ssh-key');

        SshKeyProvider::setup($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_ssh_key_if_no_argument_is_passed(): void
    {
        $profile = AwsProfileFactory::create();
        $keys = [
            SshKeyFactory::create([
                'name' => 'Key 1',
            ]),
            SshKeyFactory::create([
                'name' => 'Key 2',
            ])
        ];

        $this->sshKeyManager->method('getAvailableKeys')
            ->willReturn($keys);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select SSH key',
                [
                    'Key 1',
                    'Key 2',
                    'Other',
                ]
            )
            ->willReturn('Key 1');

        self::assertSame($keys[0], $this->subject->getSshKey($profile));
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_path_if_invalid_path_is_passed_as_argument(): void
    {
        $profile = AwsProfileFactory::create();
        $validKey = SshKeyFactory::createTmp();

        $this->sshKeyManager->method('getAvailableKeys')
            ->willReturn([$validKey]);

        $this->input->method('getArgument')
            ->with('SSH key')
            ->willReturn('invalid_ssh_key');

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select SSH key',
                [
                    $validKey->getName(),
                    'Other',
                ]
            )
            ->willReturn($validKey->getName());

        $key = $this->subject->getSshKey($profile);

        self::assertSame($validKey, $key);
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_path_if_other_ssh_key_is_chosen(): void
    {
        $profile = AwsProfileFactory::create();
        $keys = [];

        $this->sshKeyManager->method('getAvailableKeys')
            ->willReturn($keys);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select SSH key',
                [
                    'Other',
                ]
            )
            ->willReturn('Other');

        $tempKey = TempFileFactory::create();

        $this->io->expects(self::once())
            ->method('ask')
            ->with('Enter full path to ssh key')
            ->willReturn($tempKey);

        $key = $this->subject->getSshKey($profile);

        self::assertSame($tempKey, $key->getFullPath());
        self::assertSame(pathinfo($tempKey, PATHINFO_FILENAME), $key->getName());
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_path_if_invalid_path_is_provided_when_asked(): void
    {
        $profile = AwsProfileFactory::create();
        $keys = [];

        $this->sshKeyManager->method('getAvailableKeys')
            ->willReturn($keys);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select SSH key',
                [
                    'Other',
                ]
            )
            ->willReturn('Other');

        $tempKey = TempFileFactory::create();

        $this->io->expects(self::exactly(2))
            ->method('ask')
            ->with('Enter full path to ssh key')
            ->willReturnOnConsecutiveCalls('/path/ssh_key', $tempKey);

        $this->io->expects(self::once())
            ->method('error')
            ->with('Wrong ssh file "/path/ssh_key".');

        $key = $this->subject->getSshKey($profile);

        self::assertSame($tempKey, $key->getFullPath());
        self::assertSame(pathinfo($tempKey, PATHINFO_FILENAME), $key->getName());
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_last_used_key_for_profile_if_available(): void
    {
        $profile = AwsProfileFactory::create();
        $lastUsedKey = SshKeyFactory::createTmp();

        $this->sshKeyManager->method('getLastUsedKeyByProfile')
            ->willReturn($lastUsedKey);

        self::assertSame($lastUsedKey, $this->subject->getSshKey($profile));
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_ssh_key_if_last_used_is_available_and_enforced_from_option(): void
    {
        $profile = AwsProfileFactory::create();
        $lastUsedKey = SshKeyFactory::createTmp();
        $validKey = SshKeyFactory::createTmp();

        $this->sshKeyManager->method('getLastUsedKeyByProfile')
            ->willReturn($lastUsedKey);

        $this->sshKeyManager->method('getAvailableKeys')
            ->willReturn([$validKey]);

        $this->input->method('getOption')
            ->with('select-ssh-key')
            ->willReturn(true);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select SSH key',
                [
                    $validKey->getName(),
                    'Other',
                ]
            )
            ->willReturn($validKey->getName());

        $key = $this->subject->getSshKey($profile);

        self::assertSame($validKey, $key);
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_ssh_key_if_last_used_is_not_valid_file(): void
    {
        $profile = AwsProfileFactory::create();
        $lastUsedKey = SshKeyFactory::create();
        $validKey = SshKeyFactory::createTmp();

        $this->sshKeyManager->method('getLastUsedKeyByProfile')
            ->willReturn($lastUsedKey);

        $this->sshKeyManager->method('getAvailableKeys')
            ->willReturn([$validKey]);

        $this->io->expects(self::once())
            ->method('choice')
            ->with(
                'Select SSH key',
                [
                    $validKey->getName(),
                    'Other',
                ]
            )
            ->willReturn($validKey->getName());

        $key = $this->subject->getSshKey($profile);

        self::assertSame($validKey, $key);
    }


    /**
     * @test
     * @return void
     */
    public function it_provides_ssh_key_if_argument_is_passed(): void
    {
        $profile = AwsProfileFactory::create();
        $validKey = SshKeyFactory::createTmp();

        $this->input->method('getArgument')
            ->with('SSH key')
            ->willReturn($validKey->getFullPath());

        $key = $this->subject->getSshKey($profile);

        self::assertSame($validKey->getFullPath(), $key->getFullPath());
    }

    /**
     * @test
     * @return void
     */
    public function it_stores_last_used_ssh_key(): void
    {
        $profile = AwsProfileFactory::create();
        $validKey = SshKeyFactory::createTmp();

        $this->input->method('getArgument')
            ->with('SSH key')
            ->willReturn($validKey->getFullPath());

        $this->sshKeyManager->expects(self::once())
            ->method('setLastUsedKey');

        $key = $this->subject->getSshKey($profile);

        self::assertSame($validKey->getFullPath(), $key->getFullPath());
    }

}