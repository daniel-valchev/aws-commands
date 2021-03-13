<?php

namespace App\Tests\Factories;

use App\Models\EC2Instance;

class Ec2InstanceFactory extends Factory
{
    /**
     * @param array $input
     * @return EC2Instance
     */
    public static function create(
        array $input = []
    ): EC2Instance
    {
        $faker = self::getFaker();

        $name = $input['name'] ?? $faker->userName;
        $ip = $input['ip'] ?? $faker->ipv4;
        $dns = $input['dns'] ?? $faker->domainName;

        return new EC2Instance(
            $name,
            $ip,
            $dns
        );
    }

}