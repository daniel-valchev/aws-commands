<?php

namespace App\Tests\Models;

use App\Models\RdsInstance;
use App\Tests\TestCase;

class RdsInstanceTest extends TestCase
{

    /**
     * @var RdsInstance
     */
    private RdsInstance $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RdsInstance(
            'id',
            'name',
            'host.com',
            5432,
            'db.t2.micro'
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(RdsInstance::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_name(): void
    {
        self::assertSame('name', $this->subject->getName());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_id(): void
    {
        self::assertSame('id', $this->subject->getId());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_host(): void
    {
        self::assertSame('host.com', $this->subject->getHost());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_port(): void
    {
        self::assertSame(5432, $this->subject->getPort());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_instance_class(): void
    {
        self::assertSame('db.t2.micro', $this->subject->getInstanceClass());
    }

    /**
     * @test
     * @return void
     */
    public function it_has_string_representation(): void
    {
        self::assertSame(
            'name [db.t2.micro host.com:5432]',
            (string) $this->subject
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_determines_if_equals(): void
    {
        $equalInstance = new RdsInstance(
            'id', 'name', 'host.com', 5432, 'db.t2.micro'
        );

        $nonEqualInstance = new RdsInstance(
            'id', 'name 2', 'host.com', 5432, 'db.t2.micro',
        );

        $nonEqualInstance2 = new RdsInstance(
            'id', 'name', 'host2.com', 5432, 'db.t2.micro',
        );

        $nonEqualInstance3 = new RdsInstance(
            'id', 'name', 'host', 5002, 'db.t2.micro',
        );

        self::assertTrue($this->subject->equals($equalInstance));
        self::assertFalse($this->subject->equals($nonEqualInstance));
        self::assertFalse($this->subject->equals($nonEqualInstance2));
        self::assertFalse($this->subject->equals($nonEqualInstance3));
    }
}