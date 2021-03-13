<?php

namespace App\Tests\Utils;

use App\Tests\TestCase;
use App\Utils\FileUtils;

class FileUtilsTest extends TestCase
{

    /**
     * @test
     * @return void
     */
    public function resolve_path_resolves_dot_in_path(): void
    {
        $path = './path';
        $basePath = '/var/dev/test';

        $resolved = FileUtils::resolvePath($path, $basePath);

        self::assertSame(
            '/var/dev/test/path',
            $resolved
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolve_path_resolves_double_dots_in_path(): void
    {
        $path = './../../path';
        $basePath = '/var/dev/test';

        $resolved = FileUtils::resolvePath($path, $basePath);

        self::assertSame(
            '/var/path',
            $resolved
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolve_path_resolves_tilde_in_path(): void
    {
        $path = '~/test/path';
        $basePath = '/var/dev/test';

        $resolved = FileUtils::resolvePath($path, $basePath);

        self::assertSame(
            getenv('HOME') . '/test/path',
            $resolved
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolve_path_resolves_absolute_paths(): void
    {
        $path = '/test/path';
        $basePath = '/var/dev/test';

        $resolved = FileUtils::resolvePath($path, $basePath);

        self::assertSame(
            '/test/path',
            $resolved
        );
    }

    /**
     * @test
     * @return void
     */
    public function resolve_path_resolves_relative_paths(): void
    {
        $path = 'test/path';
        $basePath = '/var/dev/test';

        $resolved = FileUtils::resolvePath($path, $basePath);

        self::assertSame(
            '/var/dev/test/test/path',
            $resolved
        );
    }

}