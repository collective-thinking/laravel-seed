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
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerDisks();
        }
    }

    /**
     * @return void
     */
    private function registerCommands()
    {
        $this->commands([
            Seed::class,
            SeedMake::class,
            SeedReset::class,
            SeedRollback::class,
            SeedStatus::class,
        ]);
    }

    /**
     * @return void
     */
    private function registerDisks()
    {
        app()->config["filesystems.disks.seeders"] = [
            "driver" => "local",
            "root" => database_path("seeders"),
        ];
    }
}
