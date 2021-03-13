<?php

namespace App\Services;

use App\Models\AwsProfile;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;
use Aws\Rds\RdsClient;

class AwsApiClient
{

    /**
     * @param AwsProfile $profile
     * @return Ec2Client
     */
    public function createEc2Client(
        AwsProfile  $profile
    ): Ec2Client
    {
        $credentials = new Credentials(
            $profile->getAccessKeyId(),
            $profile->getSecretAccessKey()
        );

        return new Ec2Client([
            'version'     => 'latest',
            'region'      => $profile->getRegion() ?? 'eu-west-2',
            'credentials' => $credentials,
        ]);
    }

    /**
     * @param AwsProfile $profile
     * @return RdsClient
     */
    public function createRdsClient(
        AwsProfile  $profile
    ): RdsClient
    {
        $credentials = new Credentials(
            $profile->getAccessKeyId(),
            $profile->getSecretAccessKey()
        );

        return new RdsClient([
            'version'     => 'latest',
            'region'      => $profile->getRegion() ?? 'eu-west-2',
            'credentials' => $credentials,
        ]);
    }

}