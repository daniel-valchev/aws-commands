<?php

namespace App\Tests\Factories;

use App\Models\SshKey;

class SshKeyFactory extends Factory
{
    /**
     * @param array $input
     * @return SshKey
     */
    public static function create(
        array $input = []
    ): SshKey
    {
        $faker = self::getFaker();

        $name = $input['name'] ?? $faker->userName;
        $fullPath = $input['fullPath'] ??
            implode('/', $faker->words($faker->numberBetween(0, 4)));

        return new SshKey(
            $fullPath,
            $name
        );
    }

    /**
     * @return SshKey
     */
    public static function createTmp(): SshKey
    {
        $tempKey = TempFileFactory::create();

        $name = pathinfo($tempKey, PATHINFO_FILENAME);
        $fullPath = $tempKey;

        return new SshKey(
            $fullPath,
            $name
        );
    }

}