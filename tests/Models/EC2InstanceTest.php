<?php

namespace App\Tests\Models;

use App\Models\EC2Instance;
use App\Tests\TestCase;

class EC2InstanceTest extends TestCase
{

    /**
     * @var EC2Instance
     */
    private EC2Instance $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EC2Instance(
            'name',
            'ip',
            'dns'
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(EC2Instance::class, $this->subject);
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
    public function it_returns_ip(): void
    {
        self::assertSame('ip', $this->subject->getIp());
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_dns(): void
    {
        self::assertSame('dns', $this->subject->getDns());
    }

    /**
     * @test
     * @return void
     */
    public function it_has_string_representation(): void
    {
        self::assertSame('name [ip]', (string) $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_determines_if_equals(): void
    {
        $equalInstance = new EC2Instance(
            'name', 'ip', 'dns'
        );

        $nonEqualInstance = new EC2Instance(
            'name 2', 'ip', 'dns'
        );

        $nonEqualInstance2 = new EC2Instance(
            'name', 'ip 2', 'dns'
        );

        $nonEqualInstance3 = new EC2Instance(
            'name', 'ip', 'dns 2'
        );

        self::assertTrue($this->subject->equals($equalInstance));
        self::assertFalse($this->subject->equals($nonEqualInstance));
        self::assertFalse($this->subject->equals($nonEqualInstance2));
        self::assertFalse($this->subject->equals($nonEqualInstance3));
    }
}