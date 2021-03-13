<?php

namespace App\Tests\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;

class Factory
{

    /**
     * @return Generator
     */
    public static function getFaker(): Generator
    {
        return FakerFactory::create();
    }

}