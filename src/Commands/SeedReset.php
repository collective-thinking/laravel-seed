<?php

namespace CollectiveThinking\LaravelSeed\Commands;

use CollectiveThinking\LaravelSeed\Seeder;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRollbackingSeeds;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRunningSeeds;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SeedReset extends Command
{
    use CapableOfRollbackingSeeds;
    use CapableOfRunningSeeds;

    protected $signature = "seed:reset {--i|ignore-deleted : Don't raise errors if the rollbacked seed does not exist in disk.}";
    protected $description = "Rollback all the seeds.";

    public function __construct()
    {
        parent::__construct();

        $this->seedFileName = "";
    }

    public function handle(): void
    {
        $seedFileNames = $this->getSeedsFileNamesInReverseOrder();
        $numberOfSeedsRollbacked = 0;
        $seeds = [];
        $bar = $this->output->createProgressBar(count($seedFileNames));

        if ($seedFileNames->count() > 0) {
            $bar->start();
        }

        foreach ($seedFileNames as $seedFileName) {
            $this->seedFileName = $seedFileName;

            $this->rollbackSeed();
            $this->forgetSeed();

            $seeds[] = [
                "file" => $this->seedFileName,
            ];
            $numberOfSeedsRollbacked++;
            $bar->advance();
        }

        if ($seedFileNames->count() > 0) {
            $bar->finish();
        }

        $this->line("\n");
        $this->table(["file"], $seeds);
        $this->line("");
        $this->info("$numberOfSeedsRollbacked seed(s) rollbacked.");
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedsFileNamesInReverseOrder(): Collection
    {
        /**
         * @phpstan-ignore-next-line Call to an undefined method CollectiveThinking\LaravelSeed\Seeder::inReverseOrder()
         */
        return Seeder::query()->inReverseOrder()->pluck("seeder");
    }

    private function forgetSeed(): void
    {
        Seeder::forget($this->seedFileName);
    }
}
