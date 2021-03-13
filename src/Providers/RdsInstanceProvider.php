<?php

namespace App\Providers;

use App\Exceptions\AbortCommandException;
use App\Exceptions\NoRunningRdsInstancesFoundException;
use App\Models\AwsProfile;
use App\Models\RdsInstance;
use App\Services\RdsInstanceManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class RdsInstanceProvider
{
    /**
     * @var RdsInstanceManager
     */
    private RdsInstanceManager $rdsInstanceManager;

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @param RdsInstanceManager $rdsInstanceManager
     * @param SymfonyStyle $io
     */
    public function __construct(
        RdsInstanceManager $rdsInstanceManager,
        SymfonyStyle $io
    )
    {
        $this->rdsInstanceManager = $rdsInstanceManager;
        $this->io = $io;
    }

    /**
     * @param AwsProfile $profile
     * @return RdsInstance
     * @throws AbortCommandException
     */
    public function getRdsInstance(AwsProfile $profile): RdsInstance
    {
        $instances = $this->rdsInstanceManager->getRdsInstances($profile);

        if (!count($instances)) {
            throw new NoRunningRdsInstancesFoundException(
                'No running RDS instances found.'
            );
        }

        $chosenInstance = $this->io->choice(
            'Select RDS instance',
            array_map(
                fn(RdsInstance $instance) => $instance->__toString(),
                $instances
            ),
            $instances[0]->__toString()
        );

        return array_values(
            array_filter(
                $instances,
                fn(RdsInstance $instance) => $instance->__toString() === $chosenInstance
            )
        )[0];
    }

}