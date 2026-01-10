<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Constants\Database;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseCommand;

class MigrateCommand extends BaseCommand
{
    private array $targetConnection = Database::MIGRATION_FILES;

    public function __construct()
    {
        $migrator = app('migrator');
        $dispatcher = app('events');
        parent::__construct($migrator, $dispatcher);
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $database = $this->option('database');

        if (is_null($database)) {
            foreach ($this->targetConnection as $connectionName => $path) {
                $this->option('path', '/database/' . $path);
                $this->input->setOption('path', '/database/' . $path);
                $this->migrator->usingConnection($connectionName, function () {
                    $this->prepareDatabase();

                    // Next, we will check to see if a path option has been defined. If it has
                    // we will use the path relative to the root of this installation folder
                    // so that migrations may be run for any path within the applications.
                    $this->migrator->setOutput($this->output)
                        ->run($this->getMigrationPaths(), [
                            'pretend' => $this->option('pretend'),
                            'step' => $this->option('step'),
                        ]);

                    // Finally, if the "seed" option has been given, we will re-run the database
                    // seed task to re-populate the database, which is convenient when adding
                    // a migration and a seed at the same time, as it is only this command.
                    if ($this->option('seed') && ! $this->option('pretend')) {
                        $this->call('db:seed', [
                            '--class' => $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
                            '--force' => true,
                        ]);
                    }
                });
            }
        } else {
            $this->migrator->usingConnection($this->option('database'), function () {
                $this->prepareDatabase();

                // Next, we will check to see if a path option has been defined. If it has
                // we will use the path relative to the root of this installation folder
                // so that migrations may be run for any path within the applications.
                $this->migrator->setOutput($this->output)
                    ->run($this->getMigrationPaths(), [
                        'pretend' => $this->option('pretend'),
                        'step' => $this->option('step'),
                    ]);

                // Finally, if the "seed" option has been given, we will re-run the database
                // seed task to re-populate the database, which is convenient when adding
                // a migration and a seed at the same time, as it is only this command.
                if ($this->option('seed') && ! $this->option('pretend')) {
                    $this->call('db:seed', [
                        '--class' => $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
                        '--force' => true,
                    ]);
                }
            });
        }

        return 0;
    }
}
