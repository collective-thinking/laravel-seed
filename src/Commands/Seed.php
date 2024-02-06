<?php

namespace CollectiveThinking\LaravelSeed\Commands;

use CollectiveThinking\LaravelSeed\MigrationSeederInterface;
use CollectiveThinking\LaravelSeed\Seeder;
use CollectiveThinking\LaravelSeed\Traits\CapableOfLookingForSeeds;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRunningSeeds;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jawira\CaseConverter\Convert;
use RuntimeException;

class Seed extends Command
{
    use CapableOfLookingForSeeds;
    use CapableOfRunningSeeds;

    protected $signature = "seed";
    protected $description = "Runs the seeders that have not been run yet.";

    private int $batchNumber;

    public function __construct()
    {
        parent::__construct();

        $this->seedFileName = "";
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
            $this->createSeedersTableIfItDoesNotExistYet();

            $bar->start();
        }

        foreach ($seedFileNames as $seedFileName) {
            $this->seedFileName = $seedFileName;

            $this->runSeeder();
            $this->rememberThatSeederHaveBeenRun();

            $seeds[] = [
                "file" => $this->seedFileName,
            ];
            $numberOfSeedsRan++;
            $bar->advance();
        }

        if ($seedFileNames->count() > 0) {
            $bar->finish();
        }

        $this->line("\n");
        $this->table(["file"], $seeds);
        $this->line("");
        $this->info("{$numberOfSeedsRan} seed(s) ran.");
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFiles(): Collection
    {
        $seeders = Seeder::query()->pluck("seeder");

        return $this->getSeedFileNames()->diff($seeders);
    }

    private function runSeeder(): void
    {
        include_once $this->getAbsoluteSeederFilePath();

        $className = $this->getSeederClassName();

        /** @var MigrationSeederInterface $instance */
        $instance = new $className();

        $instance->up();
    }

    private function getSeederClassName(): string
    {
        $matches = [];
        $succeeded = preg_match("/\d+_\d+_\d+_\d+_([\w_]+)$/", $this->seedFileName, $matches);

        if ($succeeded === false) {
            throw new RuntimeException("An error occured while trying to get the name of the seeder class");
        }

        if (count($matches) !== 2) {
            throw new RuntimeException("An error occured while trying to get the name of the seeder class");
        }

        return Str::plural((new Convert($matches[1]))->toPascal());
    }

    private function getAbsoluteSeederFilePath(): string
    {
        return database_path("seeders/{$this->seedFileName}.php");
    }

    private function rememberThatSeederHaveBeenRun(): void
    {
        Seeder::query()->insert([
            "seeder" => $this->seedFileName,
            "batch" => $this->batchNumber,
        ]);
    }
}
