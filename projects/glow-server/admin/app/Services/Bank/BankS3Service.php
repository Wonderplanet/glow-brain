<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Services\EnvironmentService;
use Carbon\CarbonImmutable;

/**
 * Bank KPI f001ログサービス
 */
class BankS3Service
{
    public function __construct(
        private EnvironmentService $environmentService,
    ) {
    }

    /**
     * S3アップロードパスを生成する
     *
     * @param CarbonImmutable $executionTime 実行日時
     * @param string $formatId フォーマット
     * @param string $compressedFileName 圧縮ファイル名
     * @return string S3アップロードパス
     */
    public function generateS3UploadPath(
        CarbonImmutable $executionTime,
        string $formatId,
        string $compressedFileName
    ): string {
        // アップロードパスはUTC時間
        $utcExecutionTime = $executionTime->setTimezone('UTC');
        return sprintf(
            BankKPIConstant::S3_UPLOAD_PATH,
            $this->environmentService->getApplicationId(),
            $utcExecutionTime->format('Y'),
            $utcExecutionTime->format('m'),
            $utcExecutionTime->format('d'),
            $utcExecutionTime->format('H'),
            $formatId,
            $compressedFileName
        );
    }
}
