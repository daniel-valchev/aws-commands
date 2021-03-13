<?php

namespace App\Core;

use App\Exceptions\BindingResolveException;

class Container
{
    /**
     * @var array
     */
    private array $items = [];

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->items)) {
            throw new BindingResolveException(
                sprintf('Unable to resolve "%s"', $key)
            );
        }

        return $this->items[$key];
    }

}