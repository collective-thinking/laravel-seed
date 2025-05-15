<?php

declare(strict_types=1);

namespace CollectiveThinking\LaravelSeed\Tests;

use CollectiveThinking\LaravelSeed\Seeder;
use Illuminate\Support\Facades\Event;

class CommandSeedRollbackTest extends TestCase
{
    public function test_seeders_are_rollbacked(): void
    {
        $this->storeSeedFile('0000_00_00_000000_test_seeder.php');
        $this->storeSeedFile('1111_11_11_111111_test_seeder_2.php');

        Event::fake(['migration.up', 'migration.down']);

        Seeder::unguarded(static function () {
            Seeder::query()->create([
                'seeder' => '0000_00_00_000000_test_seeder',
                'batch' => 1,
            ]);
            Seeder::query()->create([
                'seeder' => '1111_11_11_111111_test_seeder_2',
                'batch' => 2,
            ]);
        });

        // @phpstan-ignore-next-line
        $this->artisan('seed:rollback')->assertSuccessful();

        Event::assertDispatchedTimes('migration.down');
        Event::assertNotDispatched('migration.up');

        $this->assertDatabaseHas('seeders', ['seeder' => '0000_00_00_000000_test_seeder']);
        $this->assertDatabaseMissing('seeders', ['seeder' => '1111_11_11_111111_test_seeder_2']);
    }
}
