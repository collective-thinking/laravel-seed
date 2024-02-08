<?php

declare(strict_types=1);

class TestSeeder
{
    public function up(): void
    {
        event('migration.up');
    }

    public function down(): void
    {
        event('migration.down');
    }
}
