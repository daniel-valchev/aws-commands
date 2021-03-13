<?php

namespace App\Tests\Models;

use App\Models\SshKey;
use App\Tests\TestCase;

class SshKeyTest extends TestCase
{

    /**
     * @var SshKey
     */
    private SshKey $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SshKey(
            'path',
            'name'
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(SshKey::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_full_path(): void
    {
        self::assertSame('path', $this->subject->getFullPath());
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
    public function it_has_string_representation(): void
    {
        self::assertSame('name', (string) $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_determines_if_equals(): void
    {
        $equalInstance = new SshKey(
            'path', 'name'
        );

        $nonEqualInstance = new SshKey(
            'path 2', 'name'
        );

        $nonEqualInstance2 = new SshKey(
            'path', 'name 2'
        );

        self::assertTrue($this->subject->equals($equalInstance));
        self::assertFalse($this->subject->equals($nonEqualInstance));
        self::assertFalse($this->subject->equals($nonEqualInstance2));
    }
}