<?php

namespace App\Contracts;

interface PersistenceManagerInterface
{
    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value): self;

    /**
     * @param mixed $key
     * @return mixed
     */
    public function delete($key);

    /**
     * @param mixed $key
     * @return bool
     */
    public function isset($key): bool;

    /**
     * @return array|mixed[]
     */
    public function all(): array;

    /**
     * @return void
     */
    public function clear(): void;
}