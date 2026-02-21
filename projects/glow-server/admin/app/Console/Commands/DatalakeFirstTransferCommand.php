<?php

namespace App\Console\Commands;

use App\Entities\Clock;
use App\Services\Datalake\DatalakeTransferCommandService;
use Illuminate\Console\Command;

class DatalakeFirstTransferCommand extends Command
{
    public function __construct(
        private DatalakeTransferCommandService $service,
        private Clock $clock,
    ) {
        parent::__construct();
    }


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:datalake-first-transfer-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'データレイクの基本転送機構';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // メモリ制限を増加（dumpling処理での安全マージン）
        ini_set('memory_limit', '4G');
        $env = env('APP_ENV');
        $executionTime = $this->clock->now();
        $this->service->exec($env, $executionTime);

    }
}
