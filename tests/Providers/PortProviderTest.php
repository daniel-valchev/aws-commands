<?php

namespace App\Tests\Providers;

use App\Providers\PortProvider;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class PortProviderTest extends TestCase
{

    /**
     * @var InputInterface|MockObject
     */
    private InputInterface $input;

    /**
     * @var PortProvider
     */
    private PortProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->input = $this->createMock(InputInterface::class);
        $this->subject = new PortProvider(
            $this->input
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(PortProvider::class, $this->subject);
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
            ->with('port');

        PortProvider::setup($command, 5432);
    }

    /**
     * @test
     * @return void
     */
    public function it_provides_port(): void
    {
        $port = 5455;

        $this->input->expects(self::once())
            ->method('getOption')
            ->with('port')
            ->willReturn($port);

        self::assertSame($port, $this->subject->getPort());
    }

}