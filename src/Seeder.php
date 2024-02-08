<?php

namespace CollectiveThinking\LaravelSeed;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @property int $id
 * @property string $seeder
 * @property int $batch
 */
class Seeder extends Model
{
    public function scopeMatchingSeederFileName(Builder $query, string $fileName): Builder
    {
        return $query->where('seeder', $fileName);
    }

    public function scopeMatchingBatchNumber(Builder $query, int $number): Builder
    {
        return $query->where('batch', $number);
    }

    public function scopeInReverseOrder(Builder $query): Builder|QueryBuilder
    {
        return $query->orderBy('seeder', 'desc');
    }

    public static function getNextBatchNumber(): int
    {
        return self::max('batch') + 1;
    }

    public static function forget(string $seeder): void
    {
        self::query()->where('seeder', $seeder)->delete();
    }

    public static function getBatchNumberFromSeederFileName(string $fileName): int
    {
        return self::query()->matchingSeederFileName($fileName)->firstOrFail()->batch;
    }
}
