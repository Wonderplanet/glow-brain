<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Repositories\Log\LogBankRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Bank KPI f001ログサービス
 */
class BankF001CommandService extends BankCommandService
{
    public function __construct(
        private LogBankRepository $logBankRepository,
        private BankF001Service $bankF001Service,
        BankS3Service $bankS3Service,
    ) {
        parent::__construct($bankS3Service);
    }

    public function exec(string $env, CarbonImmutable $executionTime): void
    {
        // 1時間前の00分00秒と59分59秒を計算
        $startOfPreviousHour = $executionTime->subHour()->startOfHour();
        $endOfPreviousHour = $executionTime->subHour()->endOfHour();
        $tempFileName = sprintf(
            BankKPIConstant::TEMP_FILE_NAME_FORMAT,
            BankKPIFormatType::F001->value,
            $env,
            $executionTime->format('YmdHis'),
        );
        $storage = Storage::disk(BankKPIConstant::DISK_TEMP);
        $step = 1;

        // 2025-09-29 21:00:00より前かどうかを判定
        $targetDateTime = CarbonImmutable::parse('2025-09-29 21:00:00');
        $shouldFilterByUserId = $endOfPreviousHour->isBefore($targetDateTime);

        // 処理済みのusr_user_idを記録するためのセット
        $processedUserIds = [];

        do {
            // 指定ページのログを取得
            $logs = $this->logBankRepository->fetchLogsByDateRange(
                $startOfPreviousHour,
                $endOfPreviousHour,
                ($step - 1) * BankKPIConstant::LOG_FETCH_LIMIT,
                BankKPIConstant::LOG_FETCH_LIMIT
            );

            Log::info(sprintf(
                'BankF001Command: F001ログ処理 step=%d, fetched_logs=%d',
                $step,
                $logs->count()
            ));

            // ログが空ならループを終了
            if ($logs->isEmpty()) {
                break;
            }

            // 条件に該当する場合、全ループを通してusr_user_idごとに1つのlogデータのみにフィルタリング
            if ($shouldFilterByUserId) {
                $logs = $logs->filter(function ($log) use (&$processedUserIds) {
                    if (isset($processedUserIds[$log->usr_user_id])) {
                        return false; // 既に処理済みのusr_user_idは除外
                    }
                    $processedUserIds[$log->usr_user_id] = true;
                    return true;
                });

                Log::info(sprintf(
                    'BankF001Command: F001ログ処理 step=%d, after_user_id_filter_logs=%d',
                    $step,
                    $logs->count()
                ));
            }

            // 取得したログを処理
            $logs->chunk(BankKPIConstant::INSERT_CHUNK_SIZE)
                ->each(function ($chunkLogs) use ($env, $storage, $tempFileName, $executionTime) {
                    $admBankF001s = $this->bankF001Service->createLog($env, $chunkLogs, $executionTime);
                    $storage->append(
                        $tempFileName,
                        $this->bankF001Service->formatDataRecords($admBankF001s)->implode("\n")
                    );
                });

            // 次のページ
            $step++;
            unset($logs);
        } while (true);

        if ($step === 1) {
            return;
        }

        if (!$this->compressAndUploadToS3(
            $env,
            $executionTime,
            $tempFileName,
            BankKPIConstant::DISK_F001,
            BankKPIFormatType::F001->value)) {
            throw new \Exception('f001の圧縮ファイルのs3アップロードに失敗しました');
        }
    }


}
