<?php

namespace App;

use App\Contracts\PersistenceManagerInterface;
use App\Core\Container;
use App\Services\AwsApiClient;
use App\Services\AwsProfileManager;
use App\Services\EC2InstanceManager;
use App\Services\InputFileLoader;
use App\Services\JsonPersistenceManager;
use App\Services\RdsInstanceManager;
use App\Services\SshKeyManager;
use App\Utils\FileUtils;
use Symfony\Component\Dotenv\Dotenv;

class Bootstrap
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        // load all the .env files
        (new Dotenv())->loadEnv(__DIR__ .'/../.env');
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->bindVariables();
        $this->bindObjects();
    }

    /**
     * @return void
     */
    private function bindVariables(): void
    {
        $basePath = realpath(dirname($_SERVER['SCRIPT_NAME']));

        $vars = [
            'root_folder' => FileUtils::resolvePath(
                __DIR__ . '/../', __DIR__
            ),
            'persistence_filename' => FileUtils::resolvePath(
                $_ENV['PERSISTENCE_FILENAME'], $basePath
            ),
            'output_path' => FileUtils::resolvePath(
                './data/cmd', $basePath
            ),
            'input_filename' => $_ENV['INPUT_FILENAME'],
            'ec2_default_user' => $_ENV['EC2_DEFAULT_USER'],
            'ssh_keys_directory' => FileUtils::resolvePath(
                $_ENV['SSH_KEYS_DIRECTORY'], $basePath
            ),
            'aws_profile_credentials_path' => FileUtils::resolvePath(
                $_ENV['AWS_PROFILE_CREDENTIALS_PATH'], $basePath
            ),
            'aws_profile_configuration_path' => FileUtils::resolvePath(
                $_ENV['AWS_PROFILE_CONFIGURATION_PATH'], $basePath
            )
        ];

        foreach ($vars as $key => $value) {
            $this->container->set($key, $value);
        }
    }

    /**
     * @return void
     */
    private function bindObjects(): void
    {
        $this->container->set(
            PersistenceManagerInterface::class,
            new JsonPersistenceManager($this->container->get('persistence_filename'))
        );

        $this->container->set(
            AwsProfileManager::class,
            new AwsProfileManager(
                $this->container->get(PersistenceManagerInterface::class),
                $this->container->get('aws_profile_credentials_path'),
                $this->container->get('aws_profile_configuration_path')
            )
        );

        $this->container->set(
            AwsApiClient::class,
            new AwsApiClient()
        );

        $this->container->set(
            EC2InstanceManager::class,
            new EC2InstanceManager(
                $this->container->get(AwsApiClient::class)
            )
        );

        $this->container->set(
            SshKeyManager::class,
            new SshKeyManager(
                $this->container->get(PersistenceManagerInterface::class),
                $this->container->get('ssh_keys_directory')
            )
        );

        $this->container->set(
            InputFileLoader::class,
            new InputFileLoader($this->container->get('input_filename'))
        );

        $this->container->set(
            RdsInstanceManager::class,
            new RdsInstanceManager(
                $this->container->get(AwsApiClient::class)
            )
        );
    }
}