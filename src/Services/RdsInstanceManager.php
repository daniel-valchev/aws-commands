<?php

namespace App\Services;

use App\Models\AwsProfile;
use App\Models\EC2Instance;
use App\Models\RdsInstance;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;
use Aws\Rds\RdsClient;

class RdsInstanceManager
{
    /**
     * @var AwsApiClient
     */
    private AwsApiClient $awsApiClient;

    /**
     * @param AwsApiClient $awsApiClient
     */
    public function __construct(AwsApiClient $awsApiClient)
    {
        $this->awsApiClient = $awsApiClient;
    }

    /**
     * @param AwsProfile $profile
     * @return RdsInstance[]
     */
    public function getRdsInstances(AwsProfile $profile): array
    {
        $rdsClient = $this->awsApiClient->createRdsClient($profile);

        $data = $rdsClient->describeDBInstances()->toArray();

        if (!count($data['DBInstances'] ?? [])) {
            return [];
        }

        $result = [];

        foreach ($data['DBInstances'] as $instance) {
            $name = array_reduce(
                $instance['TagList'],
                fn($carry, $value) => $value['Key'] === 'Name'
                    ? ($value['Value'] ?? $carry)
                    : $carry,
                $instance['DBInstanceIdentifier']
            );

            $result[] = new RdsInstance(
                $instance['DBInstanceIdentifier'],
                $name,
                $instance['Endpoint']['Address'],
                $instance['Endpoint']['Port'],
                $instance['DBInstanceClass']
            );
        }

        usort(
            $result,
            fn(RdsInstance $lhs, RdsInstance $rhs) => strcmp($lhs->getName(), $rhs->getName())
        );

        return $result;
    }

}