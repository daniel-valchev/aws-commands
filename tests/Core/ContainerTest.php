<?php

namespace App\Tests\Core;

use App\Core\Container;
use App\Exceptions\BindingResolveException;
use App\Tests\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(Container::class, new Container());
    }

    /**
     * @test
     * @return void
     */
    public function it_sets_binding(): void
    {
        $container = new Container();
        $container->set('test', 'test');

        self::assertSame('test', $container->get('test'));
    }

    /**
     * @test
     * @return void
     */
    public function it_throws_exception_if_binding_is_not_set(): void
    {
        $container = new Container();

        $this->expectException(BindingResolveException::class);

        $container->get('missing');
    }

}