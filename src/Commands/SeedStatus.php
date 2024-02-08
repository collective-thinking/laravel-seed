<?php

namespace CollectiveThinking\LaravelSeed\Commands;

use CollectiveThinking\LaravelSeed\Seeder;
use CollectiveThinking\LaravelSeed\Traits\CapableOfLookingForSeeds;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SeedStatus extends Command
{
    use CapableOfLookingForSeeds;

    protected $signature = 'seed:status';

    protected $description = 'Show status of seeders.';

    public function handle(): void
    {
        $seedFileNamesAndStatuses = $this->getSeedFileNamesAndStatuses();

        $this->table(['file', 'status'], $seedFileNamesAndStatuses);
        $this->line('');
        $this->line("{$seedFileNamesAndStatuses->count()} row(s) displayed.");
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function getSeedFileNamesAndStatuses(): Collection
    {
        $seedFileNamesAndStatuses = collect();
        $seedFileNamesOnDisk = $this->getSeedFileNames();
        $seedFileNamesInTable = $this->getSeedFileNamesInTable();

        foreach ($seedFileNamesOnDisk as $seedFileNameOnDisk) {
            $seedFileNamesAndStatuses->push([
                'file' => $seedFileNameOnDisk,
                'status' => $seedFileNamesInTable->contains($seedFileNameOnDisk) ? 'ran' : 'not ran',
            ]);
        }

        foreach ($seedFileNamesInTable as $seedFileNameOnTable) {
            if (! $seedFileNamesOnDisk->contains($seedFileNameOnTable)) {
                $seedFileNamesAndStatuses->push([
                    'file' => $seedFileNameOnTable,
                    'status' => 'deleted from disk',
                ]);
            }
        }

        return $seedFileNamesAndStatuses;
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFileNamesInTable(): Collection
    {
        return Seeder::query()->pluck('seeder');
    }
}
