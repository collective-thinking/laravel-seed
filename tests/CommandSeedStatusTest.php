<?php

declare(strict_types=1);

namespace CollectiveThinking\LaravelSeed\Tests;

use CollectiveThinking\LaravelSeed\Seeder;

class CommandSeedStatusTest extends TestCase
{
    public function test_seeders_statuses(): void
    {
        $this->storeSeedFile('0000_00_00_000000_test_seeder.php');
        $this->storeSeedFile('1111_11_11_111111_test_seeder_2.php');

        Seeder::unguarded(static function () {
            Seeder::query()->create([
                'seeder' => '0000_00_00_000000_test_seeder',
                'batch' => 1,
            ]);
        });

        // @phpstan-ignore-next-line
        $consoleOutput = $this->artisan('seed:status')->assertSuccessful();

        $this->assertDatabaseHas('seeders', ['seeder' => '0000_00_00_000000_test_seeder']);
        $this->assertDatabaseMissing('seeders', ['seeder' => '1111_11_11_111111_test_seeder_2']);

        $consoleOutput->expectsTable(
            ['file', 'status'],
            [
                ['0000_00_00_000000_test_seeder', 'ran'],
                ['1111_11_11_111111_test_seeder_2', 'not ran'],
            ]
        );
    }
}
