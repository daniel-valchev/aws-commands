<?php

namespace App\Providers;

use App\Exceptions\AbortCommandException;
use App\Exceptions\NoRunningEc2InstancesFoundException;
use App\Models\AwsProfile;
use App\Models\EC2Instance;
use App\Services\EC2InstanceManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class EC2InstanceProvider
{
    /**
     * @var EC2InstanceManager
     */
    private EC2InstanceManager $ec2InstanceManager;

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @param EC2InstanceManager $ec2InstanceManager
     * @param SymfonyStyle $io
     */
    public function __construct(
        EC2InstanceManager $ec2InstanceManager,
        SymfonyStyle $io
    )
    {
        $this->ec2InstanceManager = $ec2InstanceManager;
        $this->io = $io;
    }

    /**
     * @param AwsProfile $profile
     * @return EC2Instance
     * @throws AbortCommandException
     */
    public function getEC2Instance(AwsProfile $profile): EC2Instance
    {
        $instances = $this->ec2InstanceManager->getRunningInstances($profile);

        if (!count($instances)) {
            throw new NoRunningEc2InstancesFoundException(
                'No running EC2 instances found.'
            );
        }

        $chosenInstance = $this->io->choice(
            'Select EC2 instance',
            array_map(
                fn(EC2Instance $instance) => $instance->__toString(),
                $instances
            ),
            $instances[0]->__toString()
        );

        return array_values(
            array_filter(
                $instances,
                fn(EC2Instance $instance) => $instance->__toString() === $chosenInstance
            )
        )[0];
    }

}