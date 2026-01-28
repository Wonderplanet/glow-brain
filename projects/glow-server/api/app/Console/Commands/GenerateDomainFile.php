<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateDomainFile extends Command
{
    // sample command: sail artisan generate:api:domain Quest startBattle endBattle anotherAction --force
    protected $signature = 'generate:api:domain {name} {actions*} {--force}';
    protected $description = 'Create a new domain files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $name = $this->argument('name');
        $actions = $this->argument('actions');
        $force = $this->option('force');

        foreach ($actions as $action) {
            $this->createDomainFiles($name, $action, $force);
        }

        $this->info('Domain classes created successfully.');
    }

    protected function createDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            $this->info("Created directory: {$path}");
        }
    }

    protected function createDomainFiles(string $name, string $action, bool $force): void
    {
        $actionName = Str::studly($action);

        // Domain directories to be created
        $directories = [
            // "Domain/{$name}/Entities",
            "Domain/{$name}/Eloquent/Models",
            "Domain/{$name}/Repositories",
            "Domain/{$name}/Services",
            "Domain/{$name}/UseCases",
            "Domain/{$name}/ResultData",
            "Domain/{$name}/Data",
        ];

        // Create directories
        foreach ($directories as $dir) {
            $this->createDirectory(app_path($dir));
        }

        // Controller
        $this->makeFile(
            "/Http/Controllers/{$name}Controller.php",
            $name,
            $actionName,
            'Controller',
            $force
        );

        // ResponseFactory
        $this->makeFile(
            "/Http/ResponseFactories/{$name}ResponseFactory.php",
            $name,
            $actionName,
            'ResponseFactory',
            $force
        );

        // UseCase
        $this->makeFile(
            "/Domain/{$name}/UseCases/{$name}{$actionName}UseCase.php",
            $name,
            $actionName,
            'UseCase',
            $force
        );

        // ResultData
        $this->makeFile(
            "/Domain/{$name}/ResultData/{$name}{$actionName}ResultData.php",
            $name,
            $actionName,
            'ResultData',
            $force
        );
    }

    protected function makeFile(
        string $path,
        string $name,
        string $actionName,
        string $type,
        bool $force = false
    ): bool {
        $stubPath = resource_path("stubs/{$type}.stub");
        $filePath = app_path($path);

        if (file_exists($filePath) && !$force) {
            $this->info("Skipping existing file: {$filePath}");
            return true;
        }

        if (!file_exists($stubPath)) {
            $this->error("The stub file does not exist.");
            return false;
        }

        $content = file_get_contents($stubPath);
        $content = str_replace('{{name}}', $name, $content);
        $content = str_replace('{{actionName}}', $actionName, $content);

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        if (!file_put_contents($filePath, $content)) {
            $this->error("Failed to create file at {$filePath}");
            return false;
        }

        $this->info("File created at {$filePath}");
        return true;
    }
}
