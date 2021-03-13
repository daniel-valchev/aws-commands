<?php

namespace App\Tests;

use App\CommandBuilder;
use App\Commands\AwsDBT;
use App\Commands\AwSSH;
use App\Exceptions\ShouldNotHappenException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class CommandBuilderTest extends TestCase
{

    /**
     * @test
     * @return void
     */
    public function it_builds_awsdbt(): void
    {
        $commandBuilder = new CommandBuilder(
            new ArrayInput([]),
            new ConsoleOutput(),
            $this->container
        );

        $command = $commandBuilder->build(AwsDBT::class);
        self::assertInstanceOf(AwsDBT::class, $command);
    }

    /**
     * @test
     * @return void
     */
    public function it_builds_awssh(): void
    {
        $commandBuilder = new CommandBuilder(
            new ArrayInput([]),
            new ConsoleOutput(),
            $this->container
        );

        $command = $commandBuilder->build(AwSSH::class);
        self::assertInstanceOf(AwSSH::class, $command);
    }

    /**
     * @test
     * @return void
     */
    public function it_throws_exception_if_invalid_class_is_passed(): void
    {
        $commandBuilder = new CommandBuilder(
            new ArrayInput([]),
            new ConsoleOutput(),
            $this->container
        );

        $this->expectException(ShouldNotHappenException::class);
        $commandBuilder->build(self::class);
    }

}