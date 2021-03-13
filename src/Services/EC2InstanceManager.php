<?php

namespace App\Services;

use App\Contracts\PersistenceManagerInterface;
use App\Models\AwsProfile;
use App\Models\EC2Instance;
use Aws\Credentials\Credentials;
use Aws\Ec2\Ec2Client;

class EC2InstanceManager
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
     * @return EC2Instance[]
     */
    public function getRunningInstances(AwsProfile $profile): array
    {
        $ec2Client = $this->awsApiClient->createEc2Client($profile);

        $data = $ec2Client->describeInstances([
            'Filters' => [
                [
                    'Name' => 'instance-state-name',
                    'Values' => ['running'],
                ],
            ],
        ])->toArray();

        if (!count($data['Reservations'] ?? [])) {
            return [];
        }

        $result = [];

        foreach ($data['Reservations'] as $reservation) {
            foreach ($reservation['Instances'] ?? [] as $instance) {
                if (($instance['State']['Name'] ?? '') !== 'running') {
                    continue;
                }

                if (empty($instance['Tags'])) {
                    continue;
                }

                $name = array_values(array_filter(
                    $instance['Tags'],
                    fn (array $tag) => in_array($tag['Key'], ['Name', 'elasticbeanstalk:environment'], true)
                ))[0]['Value'];

                $result[] = new EC2Instance(
                    $name,
                    $instance['PublicIpAddress'],
                    $instance['PublicDnsName']
                );
            }
        }

        usort(
            $result,
            fn(EC2Instance $lhs, EC2Instance $rhs) => strcmp($lhs->getName(), $rhs->getName())
        );

        return $result;
    }

}
