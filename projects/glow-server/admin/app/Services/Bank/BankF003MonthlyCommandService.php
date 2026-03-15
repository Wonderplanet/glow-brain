<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Traits\DatabaseTransactionTrait;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;

/**
 * Bank KPI f003月別ログサービス
 */
class BankF003MonthlyCommandService extends BankCommandService
{
    use DatabaseTransactionTrait;

    public function __construct(
        private BankF003Service $bankF003Service,
        BankS3Service $bankS3Service,
    ) {
        parent::__construct($bankS3Service);
    }

    public function exec(string $env, CarbonImmutable $executionTime): void
    {
        // 前月の初日00分00秒と最終日59分59秒を計算
        $startOfPreviousMonth = $executionTime->subMonth()->startOfMonth();
        $endOfPreviousMonth = $executionTime->subMonth()->endOfMonth();
        $date = (int)$startOfPreviousMonth->format('Ym');
        $f003Entities = $this->bankF003Service->createEntities($startOfPreviousMonth, $endOfPreviousMonth, $date);
        if ($f003Entities->isEmpty()) {
            return;
        }
        $f003Entities = $f003Entities->values();

        $this->transaction(
            function () use ($f003Entities, $env, $executionTime) {
                $tempFileName = sprintf(
                    BankKPIConstant::TEMP_FILE_NAME_FORMAT,
                    BankKPIFormatType::F003->value . '_monthly',
                    $env,
                    $executionTime->format('YmdHis'),
                );
                $storage = Storage::disk(BankKPIConstant::DISK_TEMP);

                // 取得したログを処理
                $admBankF003s = $this->bankF003Service->createLog($f003Entities, $executionTime);
                $storage->append(
                    $tempFileName,
                    $this->bankF003Service->formatDataRecords($env, $admBankF003s, $executionTime)->implode("\n"),
                );
                
                if (!$this->compressAndUploadToS3(
                    $env,
                    $executionTime,
                    $tempFileName,
                    BankKPIConstant::DISK_F003_MONTHLY,
                    BankKPIFormatType::F003->value)) {
                    throw new \Exception('f003の圧縮ファイルのs3アップロードに失敗しました');
                }
            }
        );
    }


}
