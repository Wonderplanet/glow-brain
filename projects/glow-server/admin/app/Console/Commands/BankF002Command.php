<?php

namespace App\Console\Commands;

use App\Services\Bank\BankF002CommandService;
use App\Traits\DatabaseTransactionTrait;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BankF002Command extends Command
{
    use DatabaseTransactionTrait;

    public function __construct(
        private BankF002CommandService $service,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bank-f002-command {--date= : 実行対象日時 (YYYY-MM-DD HH:MM:SS format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BanK KPI f002の毎時バッチ処理';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1G');
        $env = env('APP_ENV');

        // --dateオプションが指定された場合はそれを使用、なければ現在時刻
        $dateOption = $this->option('date');
        if ($dateOption) {
            try {
                $executionTime = CarbonImmutable::parse($dateOption);
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use YYYY-MM-DD HH:MM:SS format.');
                Log::error('BankF002Command: Invalid date format provided', ['date' => $dateOption]);
                return 1;
            }
        } else {
            $executionTime = CarbonImmutable::now();
        }

        $this->transaction(
            function () use ($env, $executionTime) {
                $this->service->exec($env, $executionTime);
            }
        );
    }
}
