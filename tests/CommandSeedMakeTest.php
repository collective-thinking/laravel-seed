<?php

declare(strict_types=1);

namespace CollectiveThinking\LaravelSeed\Tests;

use function now;

class CommandSeedMakeTest extends TestCase
{
    public function test_seeder_file_created_without_model(): void
    {
        $this->freezeTime();

        $this->artisan('seed:make', ['name' => 'test_hello_world']);

        $expectedFileName = now()->format('Y_m_d_His').'_test_hello_world.php';
        $this->assertTrue($this->seederStorage->exists(now()->format('Y_m_d_His').'_test_hello_world.php'));
    }

    public function test_seeder_file_created_with_model(): void
    {
        $this->freezeTime();

        $this->artisan('seed:make', ['name' => 'test_hello_world_model', '--model' => TestModel::class]);

        $expectedFileName = now()->format('Y_m_d_His').'_test_hello_world_model.php';
        $this->assertTrue($this->seederStorage->exists($expectedFileName));
        $this->assertStringContainsString('use '.TestModel::class, $this->seederStorage->get($expectedFileName) ?? '');
    }
}
