<?php

namespace App\Services;

use App\Contracts\PersistenceManagerInterface;

class JsonPersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var string
     */
    private string $filename;

    /**
     * @var array|mixed[]
     */
    private array $data;

    /**
     * @param string $filename
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;

        if (file_exists($this->filename)) {
            if (!is_readable($this->filename)) {
                throw new \RuntimeException(sprintf('File "%s" is not readable.', $this->filename));
            }

            if (!is_writable($this->filename)) {
                throw new \RuntimeException(sprintf('File "%s" is not writeable.', $this->filename));
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getJsonData();
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $data = $this->all();
        return $data[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value): PersistenceManagerInterface
    {
        $this->data[$key] = $value;
        $this->saveData();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isset($key): bool
    {
        $data = $this->all();
        return isset($data[$key]);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        unset($this->data[$key]);
        $this->saveData();
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->getJsonData();
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->data = [];
        $this->saveData();
    }

    /**
     * @return array|mixed[]
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getJsonData(): array
    {
        if (!isset($this->data)) {
            if (!is_file($this->filename)) {
                return [];
            }

            $data = file_get_contents($this->filename);

            if ($data === false) {
                throw new \RuntimeException(
                    'Unable to get contents of the persistence file.'
                );
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            $this->data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->data;
    }

    /**
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function saveData(): void
    {
        $directory = pathinfo($this->filename, PATHINFO_DIRNAME);

        /** @noinspection MkdirRaceConditionInspection */
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \RuntimeException(sprintf('Unable to create "%s" directory.', $directory));
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        file_put_contents(
            $this->filename,
            json_encode($this->data, JSON_THROW_ON_ERROR, 512)
        );
    }

}