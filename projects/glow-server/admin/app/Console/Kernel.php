<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 管理ツール表示用アセット取り込み
        $schedule->command('app:fetch-admin-assets')->dailyAt('8:00');

        if (!app()->isProduction()) {
            // これ以降のバッチは本番環境以外では不要なのでスケジューラーを動かさない
            return;
        }

        // アカウント削除ログからプロフィールデータを削除する
        $schedule->command('app:adm-user-deletion-operate-histories-log-delete-command')->daily();

        // BanK
        $schedule->command('app:bank-f001-command')->hourlyAt(5);
        $schedule->command('app:bank-f002-command')->hourlyAt(10);
        $schedule->command('app:bank-f003-daily-command')->dailyAt('00:15');
        $schedule->command('app:bank-f003-monthly-command')->monthlyOn(1, '00:20');

        // ガシャ事後検証 ガシャのマスターデータをjson化してS3にアップロードする
        $schedule->command('app:generate-gacha-master-json')->dailyAt('01:00');
        // ガシャログをJSONファイル化してS3にアップロードする
        $schedule->command('app:generate-gacha-log-json')->dailyAt('01:00');

        // データレイク
        $schedule->command('app:datalake-first-transfer-command')->dailyAt('01:00');
        $schedule->command('app:datalake-second-transfer-command')->dailyAt('04:00');
        $schedule->command('app:datalake-slack-notification-command')->dailyAt('12:00');

        // GooglePlayの返金情報(BNE S3からVoided Purchase APIデータ)の集計
        // BNE S3のVoidedPurchaseAPIのCSVが5:00ぐらいに更新されるため余裕を持った6:00に実行
        $schedule->command('app:aggregate-google-play-refunds')->dailyAt('6:00');

        // 為替レートのスクレイピング
        $schedule->command('app:scrape-foreign-currency-daily-rate-command')->dailyAt('12:00');
        $schedule->command('app:scrape-foreign-currency-rate-command')->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
