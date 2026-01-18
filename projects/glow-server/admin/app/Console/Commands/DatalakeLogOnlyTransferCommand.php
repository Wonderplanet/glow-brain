<?php

namespace App\Console\Commands;

use App\Constants\DatalakeConstant;
use App\Entities\Clock;
use App\Services\Datalake\TidbDumpService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DatalakeLogOnlyTransferCommand extends Command
{
    public function __construct(
        private TidbDumpService $tidbDumpService,
        private Clock $clock,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:datalake-log-only-transfer {--date= : 対象日付 (YYYY-MM-DD形式、未指定時は昨日)} {--day-path= : GCSアップロード先パスの日付部分を任意の文字列で指定}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'logデータベースのみをデータレイクに転送（adm_datalake_logsチェックなし）';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // メモリ制限を増加（dumpling処理での安全マージン）
            ini_set('memory_limit', '4G');

            // 対象日付の決定
            $targetDate = $this->getTargetDate();

            $this->info("=== logデータベースのみ転送開始 ===");
            $this->info("対象日付: {$targetDate->format('Y-m-d')}");
            $this->info("実行環境: " . config('app.env'));

            Log::info("logのみデータレイク転送:開始", [
                'target_date' => $targetDate->format('Y-m-d'),
                'command' => 'DatalakeLogOnlyTransferCommand'
            ]);

            // logデータベースの処理を実行
            $dayPath = $this->option('day-path');
            $processedFiles = $this->tidbDumpService->dumpTidbTablesToJson(
                'log',
                $targetDate,
                DatalakeConstant::DISK_TEMP,
                $dayPath
            );

            $this->info("=== 処理完了 ===");
            $this->info("処理済みファイル数: {$processedFiles->count()}");

            if ($processedFiles->isNotEmpty()) {
                $this->info("転送済みファイル:");
                foreach ($processedFiles as $fileName) {
                    $this->line("  - {$fileName}");
                }
            }

            Log::info("logのみデータレイク転送:完了", [
                'target_date' => $targetDate->format('Y-m-d'),
                'processed_files_count' => $processedFiles->count(),
                'processed_files' => $processedFiles->toArray()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("処理中にエラーが発生しました: " . $e->getMessage());

            Log::error("logのみデータレイク転送:エラー", [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'db_type' => 'log',
                'target_date' => $targetDate->format('Y-m-d') ?? 'unknown'
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * 対象日付を取得
     */
    private function getTargetDate(): CarbonImmutable
    {
        $dateOption = $this->option('date');

        if ($dateOption) {
            try {
                return CarbonImmutable::createFromFormat('Y-m-d', $dateOption)->startOfDay();
            } catch (\Exception $e) {
                $this->error("日付形式が不正です。YYYY-MM-DD形式で指定してください: {$dateOption}");
                throw $e;
            }
        }

        // 未指定時は昨日
        return $this->clock->now()->subDay()->startOfDay();
    }
}
