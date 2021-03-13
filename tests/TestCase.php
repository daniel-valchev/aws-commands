<?php

namespace App\Tests;

use App\Bootstrap;
use App\Core\Container;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // @codeCoverageIgnoreStart
        $this->container = new Container();

        $bootstrap = new Bootstrap($this->container);
        $bootstrap->boot();
        // @codeCoverageIgnoreEnd
    }
}