<?php

namespace App\Tests\Services;

use App\Services\JsonPersistenceManager;
use App\Tests\Factories\TempFileFactory;
use App\Tests\TestCase;
use JsonException;

class JsonPersistenceManagerTest extends TestCase
{

    /**
     * @var JsonPersistenceManager
     */
    private JsonPersistenceManager $subject;

    /**
     * @var string
     */
    private string $tempFile;

    /**
     * @return void
     * @throws JsonException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $data = json_encode([
            'key' => 'value',
            'key2' => 'value2',
        ], JSON_THROW_ON_ERROR);

        $this->tempFile = TempFileFactory::create($data);

        $this->subject = new JsonPersistenceManager(
            $this->tempFile
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_instantiates(): void
    {
        self::assertInstanceOf(JsonPersistenceManager::class, $this->subject);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_all(): void
    {
        $all = $this->subject->all();

        self::assertCount(2, $all);
        self::assertSame('value', $all['key']);
        self::assertSame('value2', $all['key2']);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_item(): void
    {
        $value = $this->subject->get('key');

        self::assertSame('value', $value);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_default_if_item_does_not_exists(): void
    {
        $value = $this->subject->get('non-existing-key', 'default');

        self::assertSame('default', $value);
    }

    /**
     * @test
     * @return void
     * @throws JsonException
     */
    public function it_sets_item(): void
    {
        $this->subject->set('new-key', 'new-value');

        $value = $this->subject->get('new-key');
        self::assertSame('new-value', $value);

        $data = json_decode(
            file_get_contents($this->tempFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame('new-value', $data['new-key']);
    }

    /**
     * @test
     * @return void
     */
    public function it_checks_if_item_is_set(): void
    {
        self::assertTrue($this->subject->isset('key'));
        self::assertFalse($this->subject->isset('non-existing-key'));
    }

    /**
     * @test
     * @return void
     * @throws JsonException
     */
    public function it_deletes_item(): void
    {
        $this->subject->delete('key');

        $all = $this->subject->all();

        self::assertCount(1, $all);
        self::assertSame('value2', $all['key2']);
        self::assertFalse($this->subject->isset('key'));
        self::assertNull($this->subject->get('key', null));

        $data = json_decode(
            file_get_contents($this->tempFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(1, $data);
        self::assertSame('value2', $data['key2']);
    }

    /**
     * @test
     * @return void
     * @throws JsonException
     */
    public function it_clears_all_items(): void
    {
        $this->subject->clear();

        $all = $this->subject->all();

        self::assertCount(0, $all);

        $data = json_decode(
            file_get_contents($this->tempFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertCount(0, $data);
    }

}