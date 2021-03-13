<?php

namespace App;

use App\Commands\AwsDBT;
use App\Commands\AwSSH;
use App\Core\Container;
use App\Exceptions\ShouldNotHappenException;
use App\Services\AwsProfileManager;
use App\Services\EC2InstanceManager;
use App\Services\InputFileLoader;
use App\Services\RdsInstanceManager;
use App\Services\SshKeyManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandBuilder
{
    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Container $container
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Container $container
    )
    {
        $this->input = $input;
        $this->output = $output;
        $this->container = $container;
    }

    /**
     * @param string $commandClass
     * @return mixed
     */
    public function build(
        string $commandClass
    )
    {
        if (is_a(AwSSH::class, $commandClass, true)) {
            return $this->buildAwSSH($this->container);
        }

        if (is_a(AwsDBT::class, $commandClass, true)) {
            return $this->buildAwsDBT($this->container);
        }

        throw new ShouldNotHappenException(
            sprintf('Invalid command class "%s".', $commandClass)
        );
    }

    /**
     * @param Container $container
     * @return AwSSH
     */
    private function buildAwSSH(Container $container): AwSSH
    {
        return new AwSSH(
            $this->input,
            $this->output,
            $container->get(AwsProfileManager::class),
            $container->get(EC2InstanceManager::class),
            $container->get(SshKeyManager::class),
            $container->get(InputFileLoader::class),
            $container->get('output_path'),
            $container->get('ec2_default_user')
        );
    }

    /**
     * @param Container $container
     * @return AwsDBT
     */
    private function buildAwsDBT(Container $container): AwsDBT
    {
        return new AwsDBT(
            $this->input,
            $this->output,
            $container->get(AwsProfileManager::class),
            $container->get(EC2InstanceManager::class),
            $container->get(SshKeyManager::class),
            $container->get(InputFileLoader::class),
            $container->get(RdsInstanceManager::class),
            $container->get('output_path'),
            $container->get('ec2_default_user')
        );
    }

}