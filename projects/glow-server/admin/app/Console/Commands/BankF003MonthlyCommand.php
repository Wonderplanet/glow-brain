<?php

namespace App\Console\Commands;

use App\Services\Bank\BankF003MonthlyCommandService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BankF003MonthlyCommand extends Command
{
    public function __construct(
        private BankF003MonthlyCommandService $service,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bank-f003-monthly-command {--date= : 実行対象年月 (YYYY-MM format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BanK KPI f003の月次バッチ処理';

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
                $executionTime = CarbonImmutable::parse($dateOption . '-01 00:00:00');
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use YYYY-MM format.');
                Log::error('BankF003MonthlyCommand: Invalid date format provided', ['date' => $dateOption]);
                return 1;
            }
        } else {
            $executionTime = CarbonImmutable::now();
        }

        $this->service->exec($env, $executionTime);
    }
}
