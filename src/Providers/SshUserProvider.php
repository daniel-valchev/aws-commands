<?php

namespace App\Providers;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class SshUserProvider
{

    private const OPTION_SELECT_SSH_USER = 'select-ssh-user';
    private const ARGUMENT_SSH_USER = 'SSH user';

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    private string $defaultUser;

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param string $defaultUser
     */
    public function __construct(
        SymfonyStyle $io,
        InputInterface $input,
        string $defaultUser
    )
    {
        $this->io = $io;
        $this->input = $input;
        $this->defaultUser = $defaultUser;
    }

    /**
     * @param Command $command
     * @return void
     */
    public static function setup(Command $command): void
    {
        $command->addOption(
            self::OPTION_SELECT_SSH_USER,
            null,
            InputOption::VALUE_NONE,
            'Ignores the implicit argument input.. Forces selecting of SSH user.'
        );

        $command->addArgument(
            self::ARGUMENT_SSH_USER,
            InputArgument::OPTIONAL,
            'SSH user name to be used when connecting to the EC2 instance.'
        );
    }

    /**
     * @return bool
     */
    private function shouldSelectSshUser(): bool
    {
        return $this->input->getOption(self::OPTION_SELECT_SSH_USER)
            && !$this->input->getArgument(self::ARGUMENT_SSH_USER);
    }

    /**
     * @return string
     */
    public function getSshUser(): string
    {
        /** @var string|null $user */
        if ($user = $this->input->getArgument(self::ARGUMENT_SSH_USER)) {
            return $user;
        }

        if (!$this->shouldSelectSshUser()) {
            return $this->defaultUser;
        }

        /** @noinspection MissingOrEmptyGroupStatementInspection */
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while(!$user = $this->io->ask('Enter ssh user'))
            /** @noinspection SuspiciousSemicolonInspection */
        ;

        return $user;
    }
}