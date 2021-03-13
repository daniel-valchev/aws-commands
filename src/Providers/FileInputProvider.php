<?php

namespace App\Providers;

use App\Exceptions\AbortCommandException;
use App\Services\InputFileLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class FileInputProvider
{

    private const OPTION_SKIP_FILE_INPUT = 'skip-file-input';

    /**
     * @var InputFileLoader
     */
    private InputFileLoader $inputFileLoader;

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * @param InputFileLoader $inputFileLoader
     * @param InputInterface $input
     */
    public function __construct(
        InputFileLoader $inputFileLoader,
        InputInterface $input
    )
    {
        $this->inputFileLoader = $inputFileLoader;
        $this->input = $input;
    }

    /**
     * @param Command $command
     * @return void
     */
    public static function setup(Command $command): void
    {
        $command->addOption(
            self::OPTION_SKIP_FILE_INPUT,
            's',
            InputOption::VALUE_NONE,
            'Ignores arguments loading from file input.'
        );
    }


    /**
     * @return void
     * @throws AbortCommandException
     */
    public function loadInput(): void
    {
        if ($this->input->getOption(self::OPTION_SKIP_FILE_INPUT) ||
            $this->input->getOption(AwsProfileProvider::OPTION_SELECT_AWS_PROFILE) ||
            $this->input->getArgument(AwsProfileProvider::ARGUMENT_AWS_PROFILE)) {
            return;
        }

        $input = $this->inputFileLoader->getInput();

        if (!$input) {
            return;
        }

        $inputKeys = array_reduce(
            array_keys($input),
            static function ($carry, $key) {
                $carry[strtoupper($key)] = $key;
                return $carry;
            },
            []
        );

        foreach (array_keys($this->input->getArguments()) as $argument) {
            $inputKey = $inputKeys[strtoupper(str_replace(' ', '_', $argument))] ?? null;

            if ($inputKey) {
                $this->input->setArgument($argument, $input[$inputKey]);
            }
        }
    }

}