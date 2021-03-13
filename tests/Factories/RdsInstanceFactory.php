<?php

namespace App\Tests\Factories;

use App\Models\RdsInstance;

class RdsInstanceFactory extends Factory
{
    /**
     * @param array $input
     * @return RdsInstance
     */
    public static function create(
        array $input = []
    ): RdsInstance
    {
        $faker = self::getFaker();

        $id = $input['id'] ?? $faker->uuid;
        $name = $input['name'] ?? $faker->userName;
        $host = $input['host'] ?? $faker->domainName;
        $port = $input['port'] ?? $faker->numberBetween(1000, 6000);
        $instanceClass = $input['instanceClass'] ?? 'db.t2.micro';

        return new RdsInstance(
            $id,
            $name,
            $host,
            $port,
            $instanceClass
        );
    }

}