<?php

namespace App\Providers;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class PortProvider
{
    private const OPTION_PORT = 'port';

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * @param Command $command
     * @param int $defaultPort
     * @return void
     */
    public static function setup(Command $command, int $defaultPort): void
    {
        $command->addOption(
            self::OPTION_PORT,
            null,
            InputOption::VALUE_OPTIONAL,
            'The tunnel port.',
            $defaultPort
        );
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->input->getOption(self::OPTION_PORT);
    }

}