<?php

declare(strict_types=1);

namespace App\Console\Commands;

// Laravel提供のmigrate:freshコマンド

use App\Domain\Constants\Database;
use Illuminate\Database\Console\Migrations\FreshCommand as BaseCommand;

class FreshCommand extends BaseCommand
{
    private array $targetConnection = Database::MIGRATION_FILES;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $database = $this->input->getOption('database');

        $this->newLine();

        if (is_null($database)) {
            foreach ($this->targetConnection as $connectionName => $path) {
                $this->components->task('Dropping all tables in connection:' . $connectionName, fn () => $this->callSilent('db:wipe', array_filter([
                        '--database' => $connectionName,
                        '--drop-views' => $this->option('drop-views'),
                        '--drop-types' => $this->option('drop-types'),
                        '--force' => true,
                    ])) === 0);
            }
        } else {
            $this->components->task('Dropping all tables', fn () => $this->callSilent('db:wipe', array_filter([
                    '--database' => $database,
                    '--drop-views' => $this->option('drop-views'),
                    '--drop-types' => $this->option('drop-types'),
                    '--force' => true,
                ])) === 0);
        }

        $this->newLine();


        $this->call('migrate', array_filter([
            '--database' => $database,
            '--path' => $this->input->getOption('path'),
            '--realpath' => $this->input->getOption('realpath'),
            '--schema-path' => $this->input->getOption('schema-path'),
            '--force' => true,
            '--step' => $this->option('step'),
        ]));

        if ($this->laravel->bound(Dispatcher::class)) {
            $this->laravel[Dispatcher::class]->dispatch(
                new DatabaseRefreshed($database, $this->needsSeeding())
            );
        }

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }

        return 0;
    }
}
