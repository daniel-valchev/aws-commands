<?php

namespace App\Tests\Services;

use App\Exceptions\InputFileNotReadable;
use App\Services\InputFileLoader;
use App\Tests\Doubles\Services\InputFileLoaderDouble;
use App\Tests\Factories\TempFileFactory;
use App\Tests\TestCase;

class InputFileLoaderTest extends TestCase
{

    /**
     * @var InputFileLoader
     */
    private InputFileLoader $subject;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new InputFileLoader('file-name');
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(InputFileLoader::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_input(): void
    {
        self::assertInstanceOf(InputFileLoader::class, $this->subject);

        $contents = <<<CONTENTS
            SSH_KEY=key
            SSH_SECRET=secret
        CONTENTS;

        $file = TempFileFactory::create($contents);

        $this->subject = new InputFileLoaderDouble($file);

        $input = $this->subject->getInput();

        self::assertCount(2, $input);
        self::assertSame('key', $input['SSH_KEY']);
        self::assertSame('secret', $input['SSH_SECRET']);
    }

    /**
     * @test
     * @return void
     */
    public function it_throws_exception_if_file_is_not_readable(): void
    {
        $file = '/tmp/aws-commands-non-existing-file';

        $subject = new InputFileLoaderDouble($file);

        $this->expectException(InputFileNotReadable::class);
        $subject->getInput();
    }

}