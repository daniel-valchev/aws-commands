<?php

namespace App\Providers;

use App\Exceptions\AbortCommandException;
use App\Exceptions\MissingAwsProfileCredentialsException;
use App\Models\AwsProfile;
use App\Services\AwsProfileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class AwsProfileProvider
{

    public const OPTION_SELECT_AWS_PROFILE = 'select-aws-profile';
    public const ARGUMENT_AWS_PROFILE = 'AWS profile';

    /**
     * @var AwsProfileManager
     */
    private AwsProfileManager $awsProfileManager;

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * @param AwsProfileManager $awsProfileManager
     * @param SymfonyStyle $io
     * @param InputInterface $input
     */
    public function __construct(
        AwsProfileManager $awsProfileManager,
        SymfonyStyle $io,
        InputInterface $input
    )
    {
        $this->awsProfileManager = $awsProfileManager;
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
            self::OPTION_SELECT_AWS_PROFILE,
            'p',
            InputOption::VALUE_NONE,
            'Ignores the implicit argument input. Forces selecting of AWS profile.'
        );

        $command->addArgument(
            self::ARGUMENT_AWS_PROFILE,
            InputArgument::OPTIONAL,
            'AWS profile name from the ~/.aws/credentials configuration.'
        );
    }

    /**
     * @return bool
     */
    private function shouldSelectAwsProfile(): bool
    {
        return $this->input->getOption(self::OPTION_SELECT_AWS_PROFILE)
            && !$this->input->getArgument(self::ARGUMENT_AWS_PROFILE);
    }

    /**
     * @return AwsProfile
     * @throws MissingAwsProfileCredentialsException
     */
    private function selectAwsProfile(): AwsProfile
    {
        $profiles = $this->awsProfileManager->getProfiles();

        if (!count($profiles)) {
            throw new MissingAwsProfileCredentialsException(
                'Unable to find AWS profiles. ' .
                'Please make sure you have profiles listed in "~/.aws/credentials".'
            );
        }

        $lastUsedProfile = $this->awsProfileManager->getLastUsedProfile();

        $chosenProfile = $this->io->choice(
            'Select AWS profile',
            array_map(
                fn(AwsProfile $profile) => $profile->getName(),
                $profiles
            ),
            $lastUsedProfile
                ? $lastUsedProfile->getName()
                : $profiles[0]->getName()
        );

        return array_values(
            array_filter(
                $profiles,
                fn(AwsProfile $profile) => $profile->getName() === $chosenProfile
            )
        )[0];
    }

    /**
     * @return AwsProfile
     * @throws AbortCommandException
     */
    public function getAwsProfile(): AwsProfile
    {
        $profile = $this->input->getArgument(self::ARGUMENT_AWS_PROFILE)
            ? $this->awsProfileManager->findProfile(
                $this->input->getArgument(self::ARGUMENT_AWS_PROFILE)
            )
            : $this->awsProfileManager->getLastUsedProfile();

        if ($profile !== null) {
            $this->io->writeln(sprintf('Using %s profile', $profile->getName()));
        }

        if ($profile === null || $this->shouldSelectAwsProfile()) {
            $profile = $this->selectAwsProfile();
            $this->awsProfileManager->incrementUsages($profile);
        }

        if ($profile !== null) {
            $this->awsProfileManager->setLastUsedProfile($profile);
        }

        return $profile;
    }

}