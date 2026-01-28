<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Repositories\Log\LogCurrencyPaidRepository;
use App\Repositories\Log\LogStoreRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;

/**
 * Bank KPI f002ログサービス
 */
class BankF002CommandService extends BankCommandService
{
    public function __construct(
        private LogCurrencyPaidRepository $logCurrencyPaidRepository,
        private LogStoreRepository $logStoreRepository,
        private BankF002Service $bankF002Service,
        private CurrencyAdminDelegator $currencyAdminDelegator,
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
            BankKPIFormatType::F002->value,
            $env,
            $executionTime->format('YmdHis')
        );
        $storage = Storage::disk(BankKPIConstant::DISK_TEMP);

        $totalStep = 0;
        $step = 1;

        // 予め、集計範囲より1日前の月の外貨月次レートを取得
        $subDay = $startOfPreviousHour->subDay();
        $foreignCurrencyMonthlyRateEntity = $this->currencyAdminDelegator->getForeignCurrencyMonthlyRate(
            $subDay->year,
            $subDay->month
        );
        $foreignCurrencyMonthlyRateEntities = collect();
        $foreignCurrencyMonthlyRateEntities->put(
            $subDay->format('Ym'),
            $foreignCurrencyMonthlyRateEntity
        );
        do {
            // 指定ページのログを取得
            $logs = $this->logCurrencyPaidRepository->fetchLogsByDateRange(
                $startOfPreviousHour,
                $endOfPreviousHour,
                ($step - 1) * BankKPIConstant::LOG_FETCH_LIMIT,
                BankKPIConstant::LOG_FETCH_LIMIT
            );

            // ログが空ならループを終了
            if ($logs->isEmpty()) {
                break;
            }

            // 取得したログを処理
            $logs->chunk(BankKPIConstant::INSERT_CHUNK_SIZE)
                ->each(function ($chunkLogs) use (
                    $env,
                    $storage,
                    $tempFileName,
                    $executionTime,
                    $foreignCurrencyMonthlyRateEntities
                ) {
                    $admBankF002s = $this->bankF002Service->createLogByCurrencyPaids(
                        $env,
                        $chunkLogs,
                        $executionTime,
                        $foreignCurrencyMonthlyRateEntities,
                    );
                    $storage->append(
                        $tempFileName,
                        $this->bankF002Service->formatDataRecords($admBankF002s)->implode("\n")
                    );
                });

            // 次のページ
            $step++;
            $totalStep++;
            unset($logs);
        } while (true);

        $step = 1;
        $oprProducts = collect();
        do {
            // 指定ページのログを取得
            $logs = $this->logStoreRepository->fetchLogsByDateRange(
                $startOfPreviousHour,
                $endOfPreviousHour,
                ($step - 1) * BankKPIConstant::LOG_FETCH_LIMIT,
                BankKPIConstant::LOG_FETCH_LIMIT
            );

            // ログが空ならループを終了
            if ($logs->isEmpty()) {
                break;
            }

            // 取得したログを処理
            $logs->chunk(BankKPIConstant::INSERT_CHUNK_SIZE)
                ->each(function ($chunkLogs) use (
                    $env,
                    $storage,
                    $tempFileName,
                    $oprProducts,
                    $executionTime,
                    $foreignCurrencyMonthlyRateEntity,
                ) {
                    $admBankF002s = $this->bankF002Service->createLogByStores(
                        $env,
                        $chunkLogs,
                        $oprProducts,
                        $executionTime,
                        $foreignCurrencyMonthlyRateEntity,
                    );
                    $storage->append(
                        $tempFileName,
                        $this->bankF002Service->formatDataRecords($admBankF002s)->implode("\n")
                    );
                });

            // 次のページ
            $step++;
            $totalStep++;
            unset($logs);
        } while (true);

        // 一度もログが取得できなかった場合は処理を終了
        if ($totalStep === 0) {
            return;
        }

        if (!$this->compressAndUploadToS3(
            $env,
            $executionTime,
            $tempFileName,
            BankKPIConstant::DISK_F002,
            BankKPIFormatType::F002->value)) {
            //throw new \Exception('f002の圧縮ファイルのs3アップロードに失敗しました');
        }
    }


}
