<?php

declare(strict_types=1);

namespace CollectiveThinking\LaravelSeed\Tests;

use CollectiveThinking\LaravelSeed\LaravelSeedServiceProvider;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected $enablesPackageDiscoveries = true;

    protected Filesystem $seederStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seederStorage = Storage::fake('seeders');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelSeedServiceProvider::class,
        ];
    }

    protected function storeSeedFile(string $seedFilePath): void
    {
        $this->seederStorage->putFileAs(
            '.',
            __DIR__."/seeders/$seedFilePath",
            $seedFilePath
        );
    }
}
