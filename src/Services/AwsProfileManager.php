<?php

namespace App\Services;

use App\Contracts\PersistenceManagerInterface;
use App\Models\AwsProfile;

class AwsProfileManager
{

    /**
     * @var PersistenceManagerInterface
     */
    private PersistenceManagerInterface $persistence;

    /**
     * @var string
     */
    private string $profileConfigurationPath;

    /**
     * @var string
     */
    private string $profileCredentialsPath;

    /**
     * @param PersistenceManagerInterface $persistence
     * @param string $profileCredentialsPath
     * @param string $profileConfigurationPath
     */
    public function __construct(
        PersistenceManagerInterface $persistence,
        string $profileCredentialsPath,
        string $profileConfigurationPath
    )
    {
        $this->persistence = $persistence;
        $this->profileConfigurationPath = $profileConfigurationPath;
        $this->profileCredentialsPath = $profileCredentialsPath;
    }

    /**
     * @return AwsProfile[]
     */
    public function getProfiles(): array
    {
        $filepath = $this->profileCredentialsPath;

        if (!file_exists($filepath)) {
            return [];
        }

        $credentials = \Aws\parse_ini_file($filepath, true, INI_SCANNER_RAW);

        $configFilename = $this->profileConfigurationPath;
        $configProfileData = \Aws\parse_ini_file($configFilename, true, INI_SCANNER_RAW);

        foreach ($configProfileData as $name => $profile) {
            // standardize config profile names
            $name = str_replace('profile ', '', $name);
            $credentials[$name] += $profile;
        }

        $profiles = array_map(
            fn(string $name, array $data) => new AwsProfile(
                $name,
                $name,
                $data['aws_access_key_id'],
                $data['aws_secret_access_key'],
                $data['region'] ?? null,
                $this->findUsageCount($name)
            ),
            array_keys($credentials),
            $credentials
        );

        usort($profiles, static function (AwsProfile $lhs, AwsProfile $rhs) {
            return $rhs->getUsageCount() - $lhs->getUsageCount();
        });

        return $profiles;
    }

    /**
     * @param string $id
     * @return AwsProfile|null
     */
    public function findProfile(string $id): ?AwsProfile
    {
        $profiles = $this->getProfiles();

        return array_values(array_filter(
            $profiles,
            fn (AwsProfile $profile) => $profile->getId() === $id
        ))[0] ?? null;
    }

    /**
     * @return AwsProfile|null
     */
    public function getLastUsedProfile(): ?AwsProfile
    {
        $lastUsedProfileId = $this->persistence->get('last_used_profile', null);

        if ($lastUsedProfileId === null) {
            return null;
        }

        return $this->findProfile($lastUsedProfileId);
    }

    /**
     * @param AwsProfile $profile
     * @return void
     */
    public function setLastUsedProfile(AwsProfile $profile): void
    {
        $this->persistence->set('last_used_profile', $profile->getId());
    }

    /**
     * @param AwsProfile $profile
     * @return void
     */
    public function incrementUsages(AwsProfile $profile): void
    {
        $usages = $this->persistence->get('profile_usages', []);
        $usages[$profile->getName()] = ($usages[$profile->getName()] ?? 0) + 1;
        $this->persistence->set('profile_usages', $usages);
    }

    /**
     * @param string $name
     * @return int
     */
    private function findUsageCount(string $name): int
    {
        $usages = $this->persistence->get('profile_usages', []);
        return $usages[$name] ?? 0;
    }

}