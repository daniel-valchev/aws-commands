<?php

namespace App\Tests\Factories;

use App\Models\AwsProfile;

class AwsProfileFactory extends Factory
{
    /**
     * @param array $input
     * @return AwsProfile
     */
    public static function create(
        array $input = []
    ): AwsProfile
    {
        $faker = self::getFaker();

        $id = $input['id'] ?? $faker->uuid;
        $name = $input['name'] ?? $faker->userName;
        $accessKeyId = $input['accessKeyId'] ?? $faker->uuid;
        $secretAccessKey = $input['secretAccessKey'] ?? $faker->password;
        $region = $input['region'] ?? 'eu-west-2';
        $usageCount = $input['usageCount'] ?? $faker->numberBetween(0, 50);

        return new AwsProfile(
            $id,
            $name,
            $accessKeyId,
            $secretAccessKey,
            $region,
            $usageCount
        );
    }

}