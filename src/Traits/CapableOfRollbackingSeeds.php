<?php

namespace CollectiveThinking\LaravelSeed\Traits;

use Illuminate\Support\Facades\Storage;

trait CapableOfRollbackingSeeds
{
    use CapableOfRunningSeeds;

    private function rollbackSeed(): void
    {
        if (! $this->hasSeederInDisk() && ! $this->option('ignore-deleted')) {
            $this->line("\n");
            $this->error("Seeder {$this->seedFileName} does not exist in disk.  Use --ignore-deleted to skip this error message.");

            exit(1);
        }

        if (! $this->hasSeederInDisk() && $this->option('ignore-deleted') !== null) {
            return;
        }

        include_once $this->getAbsoluteSeederFilePath();

        $class = $this->getSeederClassName();

        $instance = new $class;

        // @phpstan-ignore-next-line
        $instance->down();
    }

    private function hasSeederInDisk(): bool
    {
        return Storage::disk('seeders')->exists("{$this->seedFileName}.php");
    }
}
