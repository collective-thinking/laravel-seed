<?php

namespace CollectiveThinking\LaravelSeed\Commands;

use CollectiveThinking\LaravelSeed\Seeder;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRollbackingSeeds;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRunningSeeds;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SeedRollback extends Command
{
    use CapableOfRollbackingSeeds;
    use CapableOfRunningSeeds;

    protected $signature = "seed:rollback {--i|ignore-deleted : Don't raise errors if the rollbacked seed does not exist in disk.}";
    protected $description = "Rollback all the seeds.";

    public function __construct()
    {
        parent::__construct();

        $this->seedFileName = "";
    }

    public function handle(): void
    {
        $seedFileNames = $this->getSeedFileNamesInReverseOrder();
        $numberOfRollbackedSeeds = 0;
        $seedsRollbacked = [];
        $bar = $this->output->createProgressBar(count($seedFileNames));

        if ($seedFileNames->count() > 0) {
            $bar->start();
        }

        foreach ($seedFileNames as $seedFileName) {
            $this->seedFileName = $seedFileName;

            $this->rollbackSeed();
            $this->forgetSeed();

            $seedsRollbacked[] = [
                "file" => $seedFileName,
            ];
            $numberOfRollbackedSeeds++;
            $bar->advance();
        }

        if ($seedFileNames->count() > 0) {
            $bar->finish();
        }

        $this->line("\n");
        $this->table(["file"], $seedsRollbacked);
        $this->line("");
        $this->line("$numberOfRollbackedSeeds seed(s) rollbacked.");
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFileNamesInReverseOrder(): Collection
    {
        return $this->getSeedFileNamesMatchingBatchNumber($this->getLastSeedBatchNumber());
    }

    private function forgetSeed(): void
    {
        Seeder::forget($this->seedFileName);
    }

    private function getLastSeedBatchNumber(): int
    {
        return Seeder::getBatchNumberFromSeederFileName($this->getLastSeederFileName());
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFileNamesMatchingBatchNumber(int $batchNumber): Collection
    {
        /**
         * @phpstan-ignore-next-line Call to an undefined static method CollectiveThinking\LaravelSeed\Seeder::matchingBatchNumber()
         */
        return Seeder::query()->matchingBatchNumber($batchNumber)
            ->inReverseOrder()
            ->pluck("seeder");
    }

    private function getLastSeederFileName(): string
    {
        $lastSeeder = Seeder::query()->latest("id")->first();

        if (!($lastSeeder instanceof Seeder)) {
            $this->error("No seeder ran yet, nothing to rollback.");

            exit(1);
        }

        return $lastSeeder->seeder;
    }
}
