#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Bootstrap;
use App\CommandBuilder;
use App\Commands\AwSSH;
use App\Core\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

$application = new SingleCommandApplication();
$container = new Container();
$bootstrap = new Bootstrap($container);
$bootstrap->boot();

AwSSH::setup($application);

// Fix the usage of the command displayed with "--help"
$_SERVER['argv'][0] = 'awssh';

/** @noinspection PhpUnhandledExceptionInspection */
$application
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($container) {
        $commandBuilder = new CommandBuilder($input, $output, $container);
        $command = $commandBuilder->build(AwSSH::class);

        return $command();
    })
    ->run();