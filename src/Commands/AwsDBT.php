<?php

namespace App\Commands;

use App\Exceptions\AbortCommandException;
use App\Providers\AwsProfileProvider;
use App\Providers\EC2InstanceProvider;
use App\Providers\FileInputProvider;
use App\Providers\PortProvider;
use App\Providers\RdsInstanceProvider;
use App\Providers\SshKeyProvider;
use App\Providers\SshUserProvider;
use App\Services\AwsProfileManager;
use App\Services\EC2InstanceManager;
use App\Services\InputFileLoader;
use App\Services\RdsInstanceManager;
use App\Services\SshKeyManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;

class AwsDBT
{
    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @var string
     */
    private string $outputFile;

    /**
     * @var AwsProfileProvider
     */
    private AwsProfileProvider $awsProfileProvider;

    /**
     * @var EC2InstanceProvider
     */
    private EC2InstanceProvider $ec2InstanceProvider;

    /**
     * @var SshKeyProvider
     */
    private SshKeyProvider $sshKeyProvider;

    /**
     * @var SshUserProvider
     */
    private SshUserProvider $sshUserProvider;

    /**
     * @var FileInputProvider
     */
    private FileInputProvider $fileInputProvider;

    /**
     * @var RdsInstanceProvider
     */
    private RdsInstanceProvider $rdsInstanceProvider;

    /**
     * @var PortProvider
     */
    private PortProvider $portProvider;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsProfileManager $awsProfileManager
     * @param EC2InstanceManager $ec2InstanceManager
     * @param SshKeyManager $sshKeyManager
     * @param InputFileLoader $inputFileLoader
     * @param RdsInstanceManager $rdsInstanceManager
     * @param string $outputFile
     * @param string $defaultSshUser
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        AwsProfileManager $awsProfileManager,
        EC2InstanceManager $ec2InstanceManager,
        SshKeyManager $sshKeyManager,
        InputFileLoader $inputFileLoader,
        RdsInstanceManager $rdsInstanceManager,
        string $outputFile,
        string $defaultSshUser
    )
    {
        $this->outputFile = $outputFile;
        $this->io = $io = new SymfonyStyle($input, $output);

        $this->awsProfileProvider = new AwsProfileProvider(
            $awsProfileManager, $io, $input
        );

        $this->ec2InstanceProvider = new EC2InstanceProvider(
            $ec2InstanceManager, $io
        );

        $this->sshKeyProvider = new SshKeyProvider(
            $sshKeyManager, $io, $input
        );

        $this->sshUserProvider = new SshUserProvider(
            $io, $input, $defaultSshUser
        );

        $this->fileInputProvider = new FileInputProvider(
            $inputFileLoader, $input
        );

        $this->rdsInstanceProvider = new RdsInstanceProvider(
            $rdsInstanceManager, $io
        );

        $this->portProvider = new PortProvider($input);
    }

    /**
     * @param SingleCommandApplication $application
     * @param int $defaultPort
     * @return void
     */
    public static function setup(
        SingleCommandApplication $application,
        int $defaultPort
    ): void
    {
        $application->setName('AWS RDS Easy Tunnel')
            ->setVersion('1.0.0');

        AwsProfileProvider::setup($application);
        SshUserProvider::setup($application);
        SshKeyProvider::setup($application);
        FileInputProvider::setup($application);
        PortProvider::setup($application, $defaultPort);
    }

    /**
     * @return int
     */
    public function __invoke(): int
    {
        try {
            $this->run();

            return 0;
        } catch (AbortCommandException $e) {
            $this->io->error($e->getMessage());

            return 1;
        }
    }

    /**
     * @return void
     * @throws AbortCommandException
     */
    private function run(): void
    {
        $this->fileInputProvider->loadInput();

        $profile = $this->awsProfileProvider->getAwsProfile();
        $rdsInstance = $this->rdsInstanceProvider->getRdsInstance($profile);
        $ec2Instance = $this->ec2InstanceProvider->getEC2Instance($profile);
        $sshUser = $this->sshUserProvider->getSSHUser();
        $sshKey = $this->sshKeyProvider->getSSHKey($profile);
        $port = $this->portProvider->getPort();

        $output = <<<COMMAND
            ssh -i {$sshKey->getFullPath()}
                -o UserKnownHostsFile=/dev/null
                -o StrictHostKeyChecking=no
                -o LogLevel=quiet
                -L {$port}:{$rdsInstance->getHost()}:{$rdsInstance->getPort()}
                {$sshUser}@{$ec2Instance->getDns()}
                -N
        COMMAND;

        file_put_contents(
            $this->outputFile,
            $output
        );
    }

}