<?php

declare(strict_types=1);

namespace CollectiveThinking\LaravelSeed\Tests;

use Illuminate\Support\Facades\Event;

class CommandSeedTest extends TestCase
{
    public function test_seeders_are_run(): void
    {
        $this->storeSeedFile('0000_00_00_000000_test_seeder.php');
        $this->storeSeedFile('1111_11_11_111111_test_seeder_2.php');

        Event::fake(['migration.up', 'migration.down']);

        // @phpstan-ignore-next-line
        $this->artisan('seed')->assertSuccessful();

        Event::assertDispatchedTimes('migration.up', 2);
        Event::assertNotDispatched('migration.down');

        $this->assertDatabaseHas('seeders', ['seeder' => '0000_00_00_000000_test_seeder']);
        $this->assertDatabaseHas('seeders', ['seeder' => '1111_11_11_111111_test_seeder_2']);
    }
}
