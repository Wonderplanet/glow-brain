<?php

namespace App\Console\Commands;

use App\Exceptions\ScrapeForeignCurrencyDailyRateException;
use App\Traits\DatabaseTransactionTrait;
use App\Utils\CommandUtility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;

class ScrapeForeignCurrencyDailyRateCommand extends Command
{
    use DatabaseTransactionTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-foreign-currency-daily-rate-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '本日の為替レートのスクレイピングを実行します(毎日12:00に実行されることを想定)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = CommandUtility::getNow();
        Log::info("本日の為替定期収集コマンド {$now->year}年{$now->month}月{$now->day}更新分:開始");

        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);
        try {
            $this->transaction(
                function () use (
                    $now,
                    $currencyAdminDelegator
                ) {
                    try {
                        $currencyAdminDelegator->scrapeForeignCurrencyDailyRate();
                    } catch (\Exception $e) {
                        Log::error("本日の為替定期収集コマンド {$now->year}年{$now->month}月{$now->day}更新分:エラー発生", [$e]);
                        throw new ScrapeForeignCurrencyDailyRateException('ScrapeForeignCurrencyDailyRateException From Command');
                    }
                }
            );
        } catch (\Exception $e) {
            Log::error('', [$e]);
        } finally {
            Log::info("本日の為替定期収集コマンド {$now->year}年{$now->month}月{$now->day}更新分:終了");
        }
    }
}
