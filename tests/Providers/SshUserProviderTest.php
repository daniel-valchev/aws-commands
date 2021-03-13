<?php

namespace App\Tests\Providers;

use App\Providers\SshUserProvider;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SshUserProviderTest extends TestCase
{
    /**
     * @var string
     */
    private string $defaultUser = 'user';

    /**
     * @var MockObject|SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @var MockObject|InputInterface
     */
    private InputInterface $input;

    /**
     * @var SshUserProvider
     */
    private SshUserProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->io = $this->createMock(SymfonyStyle::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->subject = new SshUserProvider(
            $this->io,
            $this->input,
            $this->defaultUser
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(SshUserProvider::class, $this->subject);
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
            ->with('SSH user');

        SshUserProvider::setup($command);
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
            ->with('select-ssh-user');

        SshUserProvider::setup($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_default_user_by_default(): void
    {
        self::assertSame($this->defaultUser, $this->subject->getSshUser());
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_user_if_passed_by_argument(): void
    {
        $user = 'argument';

        $this->input->method('getArgument')
            ->with('SSH user')
            ->willReturn($user);

        self::assertSame($user, $this->subject->getSshUser());
    }

    /**
     * @test
     * @return void
     */
    public function it_asks_for_user_if_enforced_from_option(): void
    {
        $this->input->method('getOption')
            ->with('select-ssh-user')
            ->willReturn(true);

        $this->io->expects(self::once())
            ->method('ask')
            ->with('Enter ssh user')
            ->willReturn('ssh-user');

        self::assertSame('ssh-user', $this->subject->getSshUser());
    }

}