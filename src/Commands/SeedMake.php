<?php

namespace CollectiveThinking\LaravelSeed\Commands;

use Carbon\Carbon;
use CollectiveThinking\LaravelSeed\Traits\CapableOfRunningSeeds;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeedMake extends Command
{
    use CapableOfRunningSeeds;

    protected $signature = "seed:make {name : The name of the seeder. Can have sub folders in the name.} {--f|force : Will erase any existing file.} {--m|model= : The model into which to seed data.}";
    protected $description = "Create a new seeder.";
    protected $name;

    private string $seederFilePath;
    private string $seederFileContent;
    private string $stubFilePath;

    public function __construct()
    {
        parent::__construct();

        $this->name = "";
        $this->seederFilePath = "";
        $this->seederFileContent = "";
        $this->stubFilePath = "";
    }

    public function handle(): void
    {
        $nameArgument = $this->argument("name");
        $nameArgument = is_string($nameArgument) ? $nameArgument : "";

        $this->name = $nameArgument;

        if ($this->cantEraseExistingSeeder()) {
            $this->error("A seeder already exists (use the --force option if you want to erase the existing file).");

            exit(1);
        }

        if ($this->identicalSeederFound()) {
            if (!$this->confirm("Another seeder already exist at {$this->getIdenticalSeederFilePath()}, do you still want to create this one?")) {
                $this->info("Seeder creation caneled.");

                exit(0);
            }
        }

        $this->createSeeder();

        $this->info("Seeder created at database/seeders/{$this->getFilePath()}");

        $this->createSeedersTableIfItDoesNotExistYet();
    }

    private function cantEraseExistingSeeder(): bool
    {
        return Storage::disk("seeders")->exists($this->getFilePath()) && $this->option("force") === null;
    }

    private function createSeeder(): void
    {
        if ($this->specifiedModel()) {
            $this->checkIfModelExists();
            $this->createSeederWithModel();
        } else {
            $this->createSeederWithoutModel();
        }
    }

    private function createSeederWithoutModel(): void
    {
        $this->seederFilePath = $this->getFilePath();
        $this->seederFileContent = $this->getSeederWithoutModelContent();

        $this->storeSeederInFile();
    }

    private function createSeederWithModel(): void
    {
        $this->seederFilePath = $this->getFilePath();
        $this->seederFileContent = $this->getSeederWithModelContent();

        $this->storeSeederInFile();
    }

    private function getStubWithoutModelContent(): string
    {
        $this->stubFilePath = __DIR__ . "/../stubs/SeederWithoutModel.stub";

        return $this->getStubContent();
    }

    private function getStubWithModelContent(): string
    {
        $this->stubFilePath = __DIR__ . "/../stubs/SeederWithModel.stub";

        return $this->getStubContent();
    }

    private function getSeederWithoutModelContent(): string
    {
        return str_replace("{{ class }}", $this->getClassName(), $this->getStubWithoutModelContent());
    }

    private function getSeederWithModelContent(): string
    {
        $content = str_replace("{{ class }}", $this->getClassName(), $this->getStubWithModelContent());
        $content = str_replace("{{ modelNamespace }}", $this->getModelNamespace(), $content);
        $content = str_replace("{{ modelName }}", $this->getModelName(), $content);

        return $content;
    }

    private function getClassName(): string
    {
        return Str::plural(basename($this->name));
    }

    private function getFilePath(): string
    {
        return "{$this->getTimestamp()}_{$this->getFileName()}.php";
    }

    private function getTimestamp(): string
    {
        return Carbon::now()->format("Y_m_d_his");
    }

    private function getFileName(): string
    {
        return Str::snake(Str::plural($this->name));
    }

    private function specifiedModel(): bool
    {
        return $this->option("model") !== null;
    }

    private function getModelNamespace(): string
    {
        $model = $this->option("model");
        $model = is_string($model) ? $model : "";

        $laravelVersion = (int) app()->version();

        if ($laravelVersion === 8) {
            return "App\\Models\\$model";
        }

        return "App\\$model";
    }

    private function getModelName(): string
    {
        $model = $this->option("model");
        $model = is_string($model) ? $model : "";

        return basename($model);
    }

    private function checkIfModelExists(): void
    {
        $modelNamespace = $this->getModelNamespace();

        if (!class_exists($modelNamespace)) {
            $this->error("model $modelNamespace does not exist");

            exit(4);
        }
    }

    private function storeSeederInFile(): void
    {
        $written = Storage::disk("seeders")->put($this->seederFilePath, $this->seederFileContent);

        if (!$written) {
            $this->error("Seeder could not be created.");

            exit(4);
        }
    }

    private function getStubContent(): string
    {
        $content = file_get_contents($this->stubFilePath);

        if ($content === false) {
            $this->error("Could not retrieve the Seeder stub file content.");

            exit(3);
        }

        return $content;
    }

    private function identicalSeederFound(): bool
    {
        return collect(Storage::disk("seeders")->files())->filter(function ($path) {
            return Str::endsWith($path, "{$this->getFileName()}.php");
        })->count() > 0;
    }

    private function getIdenticalSeederFilePath(): string
    {
        return collect(Storage::disk("seeders")->files())->filter(function ($path) {
            return Str::endsWith($path, "{$this->getFileName()}.php");
        })->first();
    }
}
