<?php

namespace CollectiveThinking\LaravelSeed\Commands;

use CollectiveThinking\LaravelSeed\Seeder;
use CollectiveThinking\LaravelSeed\Traits\CapableOfLookingForSeeds;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRunningSeeds;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Seed extends Command
{
    use CapableOfLookingForSeeds;
    use CapableOfRunningSeeds;

    protected $signature = 'seed';

    protected $description = 'Runs the seeders that have not been run yet.';

    private int $batchNumber;

    public function __construct()
    {
        parent::__construct();

        $this->seedFileName = '';
        $this->batchNumber = 0;
    }

    public function handle(): void
    {
        $this->batchNumber = Seeder::getNextBatchNumber();

        $seedFileNames = $this->getSeedFiles();
        $numberOfSeedsRan = 0;
        $seeds = [];
        $bar = $this->output->createProgressBar(count($seedFileNames));

        if ($seedFileNames->count() > 0) {
            $bar->start();
        }

        foreach ($seedFileNames as $seedFileName) {
            $this->seedFileName = $seedFileName;

            $this->runSeeder();
            $this->rememberThatSeederHaveBeenRun();

            $seeds[] = [
                'file' => $this->seedFileName,
            ];
            $numberOfSeedsRan++;
            $bar->advance();
        }

        if ($seedFileNames->count() > 0) {
            $bar->finish();
        }

        $this->line("\n");
        $this->table(['file'], $seeds);
        $this->line('');
        $this->info("{$numberOfSeedsRan} seed(s) ran.");
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFiles(): Collection
    {
        $seeders = Seeder::query()->pluck('seeder');

        return $this->getSeedFileNames()->diff($seeders);
    }

    private function runSeeder(): void
    {
        include_once $this->getAbsoluteSeederFilePath();

        $className = $this->getSeederClassName();

        $instance = new $className;

        // @phpstan-ignore-next-line
        $instance->up();
    }

    private function getAbsoluteSeederFilePath(): string
    {
        return Storage::disk('seeders')->path("{$this->seedFileName}.php");
    }

    private function rememberThatSeederHaveBeenRun(): void
    {
        Seeder::query()->insert([
            'seeder' => $this->seedFileName,
            'batch' => $this->batchNumber,
        ]);
    }
}
