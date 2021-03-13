<?php

namespace App\Services;

use App\Contracts\PersistenceManagerInterface;
use App\Models\AwsProfile;
use App\Models\SshKey;

class SshKeyManager
{

    /**
     * @var PersistenceManagerInterface
     */
    private PersistenceManagerInterface $persistence;

    /**
     * @var string
     */
    private string $keysDirectory;

    /**
     * @param PersistenceManagerInterface $persistence
     * @param string $keysDirectory
     */
    public function __construct(
        PersistenceManagerInterface $persistence,
        string $keysDirectory
    )
    {
        $this->persistence = $persistence;
        $this->keysDirectory = $keysDirectory;
    }

    /**
     * @return SshKey[]
     */
    public function getAvailableKeys(): array
    {
        $directory = $this->keysDirectory;
        $excludedFiles = ['.', '..', 'config', '.DS_Store', 'known_hosts'];
        $excludedExtensions = ['pub', 'pem'];

        $files = array_values(array_filter(
            $this->getDirectoryFiles(),
            fn(string $fileName) =>
                !in_array($fileName, $excludedFiles, true) &&
                !in_array(pathinfo($fileName, PATHINFO_EXTENSION), $excludedExtensions, true)
        ));

        return array_map(
            fn(string $fileName) => new SshKey("{$directory}/{$fileName}", $fileName),
            $files
        );
    }

    /**
     * @param AwsProfile $profile
     * @return SshKey|null
     */
    public function getLastUsedKeyByProfile(AwsProfile $profile): ?SshKey
    {
        $lastUsedKeys = $this->persistence->get('last_used_keys', []);
        $keyFullPath = $lastUsedKeys[$profile->getId()] ?? null;

        if ($keyFullPath === null) {
            return null;
        }

        $keyName = pathinfo($keyFullPath, PATHINFO_FILENAME);
        return new SshKey($keyFullPath, $keyName);
    }

    /**
     * @param AwsProfile $profile
     * @param SshKey $key
     * @return void
     */
    public function setLastUsedKey(AwsProfile $profile, SshKey $key): void
    {
        $lastUsedKeys = $this->persistence->get('last_used_keys', []);
        $lastUsedKeys[$profile->getId()] = $key->getFullPath();

        $this->persistence->set('last_used_keys', $lastUsedKeys);
    }

    /**
     * @return array|string[]
     */
    protected function getDirectoryFiles(): array
    {
        $directory = $this->keysDirectory;
        $files = scandir($directory);

        if ($files === false) {
            throw new \InvalidArgumentException(
                sprintf('Unable to scan directory "%s"', $directory)
            );
        }

        return $files;
    }

}