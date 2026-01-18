<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateModelFile extends Command
{
    // sample command: sail artisan generate:api:model Unit MstUnitGradeUps MstUnits --force
    protected $signature = 'generate:api:model {domainName} {tableNames*} {--force} {--migrate} {--client-only}';
    protected $description = 'Create a new model files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $domainName = $this->argument('domainName');
        $tableNames = $this->argument('tableNames');
        $force = $this->option('force');

        $now = CarbonImmutable::now();
        foreach ($tableNames as $tableName) {
            $now = $now->addSeconds(1);
            $migrationCreateFileName = $this->makeMigrationCreateFileName($now, $tableName);

            $dbName = $this->getFirstPartOfCamelCase($tableName);
            switch ($dbName) {
                case 'Mst':
                    $this->createMstModelFiles($domainName, $tableName, $force);
                    $this->createMigrationFile(
                        $domainName,
                        $tableName,
                        'MstMigration',
                        $force,
                        $migrationCreateFileName
                    );
                    break;
                case 'Opr':
                    $this->createOprModelFiles($domainName, $tableName, $force);
                    $this->createMigrationFile(
                        $domainName,
                        $tableName,
                        'OprMigration',
                        $force,
                        $migrationCreateFileName
                    );
                    break;
                case 'Usr':
                    $this->createUsrModelFiles($domainName, $tableName, $force);
                    $this->createMigrationFile(
                        $domainName,
                        $tableName,
                        'UsrMigration',
                        $force,
                        $migrationCreateFileName
                    );
                    break;
                default:
                    $this->error("Unknown db name: {$dbName}");
                    break;
            }
        }

        $this->info('Model classes created successfully.');
    }

    protected function makeMigrationCreateFileName(CarbonImmutable $now, string $tableName): string
    {
        $tableNameSnake = Str::plural(Str::snake($tableName));
        return $now->format('Y_m_d_His') . '_create_' . $tableNameSnake . '_table.php';
    }

    protected function getFirstPartOfCamelCase(string $string): string
    {
        if (preg_match('/^[A-Z][^A-Z]*/', $string, $matches)) {
            return $matches[0];
        }
        return $string;
    }

    protected function createDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            $this->info("Created directory: {$path}");
        }
    }

    protected function createMstModelFiles(string $domainName, string $tableName, bool $force): void
    {
        $tableName = Str::studly($tableName);
        $tableName = Str::singular($tableName);

        // Domain directories to be created
        $directories = [
            "Domain/Resource/Mst/Models",
            "Domain/Resource/Mst/Entities",
            "Domain/Resource/Mst/Repositories",
        ];

        // Create directories
        foreach ($directories as $dir) {
            $this->createDirectory(app_path($dir));
        }

        // MstModel
        if ($this->option('client-only')) {
            $this->makeFile(
                "/Domain/Resource/Mst/Models/{$tableName}.php",
                $domainName,
                $tableName,
                'MstModelNoEntity',
                $force
            );
        } else {
            $this->makeFile(
                "/Domain/Resource/Mst/Models/{$tableName}.php",
                $domainName,
                $tableName,
                'MstModel',
                $force
            );
        }

        // MstModelEntity
        if ($this->option('client-only') === false) {
            $this->makeFile(
                "/Domain/Resource/Mst/Entities/{$tableName}Entity.php",
                $domainName,
                $tableName,
                'MstModelEntity',
                $force
            );
        }

        // MstModelRepository
        if ($this->option('client-only') === false) {
            $this->makeFile(
                "/Domain/Resource/Mst/Repositories/{$tableName}Repository.php",
                $domainName,
                $tableName,
                'MstModelRepository',
                $force
            );
        }

        // MstModelFactory
        if ($this->option('client-only') === false) {
            $this->makeFile(
                "../database/factories/{$tableName}Factory.php",
                $domainName,
                $tableName,
                'MstModelFactory',
                $force
            );
        }
    }

    protected function createOprModelFiles(
        string $domainName,
        string $tableName,
        bool $force
    ): void {
        $this->createMstModelFiles($domainName, $tableName, $force);
    }

    protected function createUsrModelFiles(
        string $domainName,
        string $tableName,
        bool $force
    ): void {
        $tableName = Str::studly($tableName);
        $tableName = Str::singular($tableName);

        // Domain directories to be created
        $directories = [
            "Domain/{$domainName}/Models",
            "Domain/{$domainName}/Repositories",
            "Domain/Resource/Usr/Entities",
        ];

        // Create directories
        foreach ($directories as $dir) {
            $this->createDirectory(app_path($dir));
        }

        // UsrModel
        $this->makeFile(
            "/Domain/{$domainName}/Models/{$tableName}.php",
            $domainName,
            $tableName,
            'UsrModel',
            $force
        );

        // UsrModelInterface
        $this->makeFile(
            "/Domain/{$domainName}/Models/{$tableName}Interface.php",
            $domainName,
            $tableName,
            'UsrModelInterface',
            $force
        );

        // UsrModelEntity
        $this->makeFile(
            "/Domain/Resource/Usr/Entities/{$tableName}Entity.php",
            $domainName,
            $tableName,
            'UsrModelEntity',
            $force
        );

        // UsrModelRepository
        $this->makeFile(
            "/Domain/{$domainName}/Repositories/{$tableName}Repository.php",
            $domainName,
            $tableName,
            'UsrModelRepository',
            $force
        );

        // UsrModelFactory
        $this->makeFile(
            "../database/factories/{$tableName}Factory.php",
            $domainName,
            $tableName,
            'UsrModelFactory',
            $force
        );
    }

    protected function createMigrationFile(
        string $domainName,
        string $tableName,
        string $type,
        bool $force,
        string $migrationCreateFileName
    ): void {
        $migrate = $this->option('migrate');
        if (!$migrate) {
            return;
        }

        $this->makeFile(
            "../database/migrations/{$migrationCreateFileName}",
            $domainName,
            $tableName,
            $type,
            $force
        );
    }

    protected function makeFile(
        string $path,
        string $domainName,
        string $tableName,
        string $type,
        bool $force = false,
    ): bool {
        $tableNameSnake = Str::plural(Str::snake($tableName));

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
        $content = str_replace('{{domainName}}', $domainName, $content);
        $content = str_replace('{{tableName}}', $tableName, $content);
        $content = str_replace('{{tableNameSnake}}', $tableNameSnake, $content);

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
