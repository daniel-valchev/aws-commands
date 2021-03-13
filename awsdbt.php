#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Bootstrap;
use App\Commands\AwsDBT;
use App\CommandBuilder;
use App\Core\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

$application = new SingleCommandApplication();
$container = new Container();
$bootstrap = new Bootstrap($container);
$bootstrap->boot();

AwsDBT::setup($application, $_ENV['AWSDBT_DEFAULT_TUNNEL_PORT']);

// Fix the usage of the command displayed with "--help"
$_SERVER['argv'][0] = 'awsdbt';

/** @noinspection PhpUnhandledExceptionInspection */
$application
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($container) {
        $commandBuilder = new CommandBuilder($input, $output, $container);
        $command = $commandBuilder->build(AwsDBT::class);

        return $command();
    })
    ->run();