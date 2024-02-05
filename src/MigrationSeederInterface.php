<?php

declare(strict_types=1);

namespace CollectiveThinking\LaravelSeed;

interface MigrationSeederInterface
{
    public function up(): void;
    public function down(): void;
}
