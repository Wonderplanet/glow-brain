<?php

namespace App\Console\Commands;

use App\Services\Bank\BankF003DailyCommandService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BankF003DailyCommand extends Command
{
    public function __construct(
        private BankF003DailyCommandService $service,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bank-f003-daily-command {--date= : 実行対象日付 (YYYY-MM-DD format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BanK KPI f003の日次バッチ処理';

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
                $executionTime = CarbonImmutable::parse($dateOption . ' 00:00:00');
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use YYYY-MM-DD format.');
                Log::error('BankF003DailyCommand: Invalid date format provided', ['date' => $dateOption]);
                return 1;
            }
        } else {
            $executionTime = CarbonImmutable::now();
        }

        $this->service->exec($env, $executionTime);
    }
}
