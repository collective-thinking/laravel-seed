<?php

namespace CollectiveThinking\LaravelSeed;

use CollectiveThinking\LaravelSeed\Commands\Seed;
use CollectiveThinking\LaravelSeed\Commands\SeedMake;
use CollectiveThinking\LaravelSeed\Commands\SeedReset;
use CollectiveThinking\LaravelSeed\Commands\SeedRollback;
use CollectiveThinking\LaravelSeed\Commands\SeedStatus;
use Illuminate\Support\ServiceProvider;

class LaravelSeedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerDisks();
        }
    }

    private function registerCommands(): void
    {
        $this->commands([
            Seed::class,
            SeedMake::class,
            SeedReset::class,
            SeedRollback::class,
            SeedStatus::class,
        ]);
    }

    private function registerDisks(): void
    {
        config('filesystems.disks.seeders', [
            'driver' => 'local',
            'root' => database_path('seeders'),
            'throw' => false,
        ]);
    }
}
