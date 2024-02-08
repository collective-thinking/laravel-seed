<?php

namespace CollectiveThinking\LaravelSeed\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function array_slice;
use function class_exists;
use function explode;
use function implode;

trait CapableOfRunningSeeds
{
    private string $seedFileName;

    private function getAbsoluteSeederFilePath(): string
    {
        return Storage::disk('seeders')->path("{$this->seedFileName}.php");
    }

    private function getSeederClassName(): string
    {
        $className = Str::studly(implode('_', array_slice(explode('_', $this->seedFileName), 4)));

        if (class_exists($className)) {
            return $className;
        }

        return Str::plural($className);
    }
}
