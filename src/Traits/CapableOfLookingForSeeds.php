<?php

namespace CollectiveThinking\LaravelSeed\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait CapableOfLookingForSeeds
{
    /**
     * @return Collection<int, string>
     */
    private function getSeedFilePaths(): Collection
    {
        return collect(Storage::disk('seeders')->files())
            ->filter(function ($path) {
                return $path[0] !== 1;
            });
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFileNames(): Collection
    {
        return $this->getSeedFilePaths()->map(function ($path) {
            return preg_replace("/\.php$/", '', $path);
        });
    }
}
