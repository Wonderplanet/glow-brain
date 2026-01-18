<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create {connection} {database : The name of the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $connection = $this->argument('connection');
        $database = $this->argument('database');

        $connectionKey = "database.connections.{$connection}";
        if (!config()->has($connectionKey)) {
            $this->error("Connection [{$connection}] does not exist.");
            return 1;
        }
        $tmpConnectionName = 'tmp_connection';
        $tmpConnectionKey = "database.connections.{$tmpConnectionName}";
        config([$tmpConnectionKey => config($connectionKey)]);
        config([$tmpConnectionKey . '.database' => null]);

        DB::connection($tmpConnectionName)->statement('CREATE DATABASE IF NOT EXISTS ' . $database);

        return 0;
    }
}
