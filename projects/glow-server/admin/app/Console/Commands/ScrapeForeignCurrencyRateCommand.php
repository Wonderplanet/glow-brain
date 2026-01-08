<?php

namespace App\Console\Commands;

use App\Exceptions\ScrapeForeignCurrencyRateException;
use App\Traits\DatabaseTransactionTrait;
use App\Utils\CommandUtility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;

class ScrapeForeignCurrencyRateCommand extends Command
{
    use DatabaseTransactionTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-foreign-currency-rate-command'
    . ' {--yearMonth= : YYYY-MM形式（例：2023-01）の年月をオプションで指定できます。指定しない場合は現在の年月が使用されます}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '外貨為替レートのスクレイピングを実行します(毎日0:00に実行されることを想定)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yearMonth = $this->option('yearMonth');
        $errorMsg = CommandUtility::validateYearMonth($yearMonth);
        if ($errorMsg !== '') {
            // エラーの場合はエラー内容を表示して処理を終える
            $this->error($errorMsg);
            exit;
        }

        // オプションの指定があればそちらを、なければ現在日時からデフォルト値を取得する
        [$year, $month] = CommandUtility::defaultYearAndMonth();
        if (!is_null($yearMonth)) {
            [$year, $month] = explode('-', $yearMonth);
        }

        Log::info("外貨為替定期収集コマンド {$year}年{$month}月末更新分:開始");

        /** @var CurrencyAdminDelegator $currencyAdminDelegator */
        $currencyAdminDelegator = app()->make(CurrencyAdminDelegator::class);
        $existsStatus = $currencyAdminDelegator->existsScrapeForeignCurrencyRateByYearAndMonth($year, $month);
        if ($existsStatus['existForeignRate']) {
            Log::info("外貨為替定期収集コマンド {$year}年{$month}月末更新分:取得済みの為終了");
            exit;
        }

        try {
            $this->transaction(
                function () use (
                    $year,
                    $month,
                    $currencyAdminDelegator
                ) {
                    try {
                        $currencyAdminDelegator->scrapeForeignCurrencyRate($year, $month);
                    } catch (\Exception $e) {
                        Log::error("外貨為替定期収集コマンド {$year}年{$month}月末更新分:エラー発生", [$e]);
                        throw new ScrapeForeignCurrencyRateException('ScrapeForeignCurrencyRateException From Command');
                    }
                }
            );
        } catch (\Exception $e) {
            Log::error('', [$e]);
        } finally {
            Log::info("外貨為替定期収集コマンド {$year}年{$month}月末更新分:終了");
        }
    }
}
