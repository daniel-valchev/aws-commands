<?php

namespace App\Tests\Providers;

use App\Providers\FileInputProvider;
use App\Services\InputFileLoader;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class FileInputProviderTest extends TestCase
{
    /**
     * @var InputFileLoader|MockObject
     */
    private InputFileLoader $inputFileLoader;

    /**
     * @var InputInterface|MockObject
     */
    private InputInterface $input;

    /**
     * @var FileInputProvider
     */
    private FileInputProvider $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->input = $this->createMock(InputInterface::class);
        $this->inputFileLoader = $this->createMock(InputFileLoader::class);
        $this->subject = new FileInputProvider(
            $this->inputFileLoader,
            $this->input
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(FileInputProvider::class, $this->subject);
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
            ->with('skip-file-input');

        FileInputProvider::setup($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_sets_argument_if_config_is_found(): void
    {
        $arguments = [
            'profile' => 'test',
            'key' => 'id_rsa',
            'missing' => 'missing',
        ];

        $commandArguments = [
            'profile' => null,
            'key' => null,
        ];

        $this->inputFileLoader->method('getInput')->willReturn($arguments);
        $this->input->method('getArguments')->willReturn($commandArguments);

        $this->input->expects(self::exactly(2))
            ->method('setArgument')
            ->withConsecutive(['profile'], ['key']);

        $this->subject->loadInput();
    }

    /**
     * @test
     * @return void
     */
    public function it_does_not_set_arguments_if_config_is_not_found(): void
    {
        $commandArguments = [
            'profile' => null,
            'key' => null,
        ];

        $this->input->expects(self::once())
            ->method('getArgument')
            ->with('AWS profile')
            ->willReturn('test');

        $this->inputFileLoader->method('getInput')->willReturn([]);
        $this->input->method('getArguments')->willReturn($commandArguments);

        $this->input->expects(self::never())->method('setArgument');

        $this->subject->loadInput();
    }

    /**
     * @test
     * @return void
     */
    public function it_does_not_set_arguments_if_aws_profile_argument_is_passed(): void
    {
        $arguments = [
            'profile' => 'test',
            'key' => 'id_rsa',
            'missing' => 'missing',
        ];

        $commandArguments = [
            'profile' => null,
            'key' => null,
        ];

        $this->input->expects(self::once())
            ->method('getArgument')
            ->with('AWS profile')
            ->willReturn('test');

        $this->inputFileLoader->method('getInput')->willReturn($arguments);
        $this->input->method('getArguments')->willReturn($commandArguments);

        $this->input->expects(self::never())->method('setArgument');

        $this->subject->loadInput();
    }

    /**
     * @test
     * @return void
     */
    public function it_does_not_set_arguments_if_select_aws_profile_option_is_passed(): void
    {
        $arguments = [
            'profile' => 'test',
            'key' => 'id_rsa',
            'missing' => 'missing',
        ];

        $commandArguments = [
            'profile' => null,
            'key' => null,
        ];

        $this->input->method('getOption')
            ->willReturn(function($option) {
                return $option === 'select-aws-profile';
            });

        $this->inputFileLoader->method('getInput')->willReturn($arguments);
        $this->input->method('getArguments')->willReturn($commandArguments);

        $this->input->expects(self::never())->method('setArgument');

        $this->subject->loadInput();
    }

    /**
     * @test
     * @return void
     */
    public function it_does_not_set_arguments_if_configured_to_skip_file_input(): void
    {
        $arguments = [
            'profile' => 'test',
            'key' => 'id_rsa',
            'missing' => 'missing',
        ];

        $commandArguments = [
            'profile' => null,
            'key' => null,
        ];

        $this->input->expects(self::once())
            ->method('getOption')
            ->with('skip-file-input')
            ->willReturn(true);

        $this->inputFileLoader->method('getInput')->willReturn($arguments);
        $this->input->method('getArguments')->willReturn($commandArguments);

        $this->input->expects(self::never())->method('setArgument');

        $this->subject->loadInput();
    }

}