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
        return collect(Storage::disk("seeders")->files());
    }

    /**
     * @return Collection<int, string>
     */
    private function getSeedFileNames(): Collection
    {
        return $this->getSeedFilePaths()->map(function ($path) {
            return preg_replace("/\.php$/", "", $path);
        });
    }
}
