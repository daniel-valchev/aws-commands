<?php

namespace App\Providers;

use App\Models\AwsProfile;
use App\Models\SshKey;
use App\Services\SshKeyManager;
use App\Utils\FileUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class SshKeyProvider
{

    private const OPTION_SELECT_SSH_KEY = 'select-ssh-key';
    private const ARGUMENT_SSH_KEY = 'SSH key';

    /**
     * @var SshKeyManager
     */
    private SshKeyManager $sshKeyManager;

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * @param SshKeyManager $sshKeyManager
     * @param SymfonyStyle $io
     * @param InputInterface $input
     */
    public function __construct(
        SshKeyManager $sshKeyManager,
        SymfonyStyle $io,
        InputInterface $input
    )
    {
        $this->sshKeyManager = $sshKeyManager;
        $this->io = $io;
        $this->input = $input;
    }

    /**
     * @param Command $command
     * @return void
     */
    public static function setup(Command $command): void
    {
        $command->addOption(
            self::OPTION_SELECT_SSH_KEY,
            null,
            InputOption::VALUE_NONE,
            'Ignores the implicit argument input. Forces selecting of SSH key.'
        );

        $command->addArgument(
            self::ARGUMENT_SSH_KEY,
            InputArgument::OPTIONAL,
            'Absolute path to SSH key to be used when connecting to the EC2 instance.'
        );
    }

    /**
     * @return bool
     */
    private function shouldSelectSshKey(): bool
    {
        return $this->input->getOption(self::OPTION_SELECT_SSH_KEY);
    }

    /**
     * @param AwsProfile $profile
     * @return SshKey
     */
    public function getSshKey(AwsProfile $profile): SshKey
    {
        $key = $this->input->getArgument(self::ARGUMENT_SSH_KEY)
            ? new SshKey(
                $this->input->getArgument(self::ARGUMENT_SSH_KEY),
                pathinfo($this->input->getArgument(self::ARGUMENT_SSH_KEY), PATHINFO_FILENAME)
            )
            : $this->sshKeyManager->getLastUsedKeyByProfile($profile);

        if (!$key || $this->shouldSelectSshKey() || !is_file($key->getFullPath())) {
            $key = $this->selectSSHKey();
        }

        $this->sshKeyManager->setLastUsedKey($profile, $key);

        return $key;
    }

    /**
     * @return SshKey
     */
    private function selectSSHKey(): SshKey
    {
        $keys = $this->sshKeyManager->getAvailableKeys();
        $keyOptions = array_merge(array_map(
            fn(SshKey $key) => $key->__toString(),
            $keys
        ), ['Other']);

        $chosenKey = $this->io->choice(
            'Select SSH key',
            $keyOptions,
            $keyOptions[0]
        );

        if ($chosenKey === 'Other') {
            $keyPath = $this->io->ask('Enter full path to ssh key');
            $keyPath = FileUtils::resolvePath($keyPath, true);

            while (!is_file(realpath($keyPath))) {
                $this->io->error(sprintf('Wrong ssh file "%s".',  $keyPath));
                $keyPath = $this->io->ask('Enter full path to ssh key');
                $keyPath = FileUtils::resolvePath($keyPath, true);
            }

            $keyName = pathinfo($keyPath, PATHINFO_FILENAME);
            return new SshKey($keyPath, $keyName);
        }

        return array_values(
            array_filter(
                $keys,
                fn(SshKey $key) => $key->__toString() === $chosenKey
            )
        )[0];
    }

}