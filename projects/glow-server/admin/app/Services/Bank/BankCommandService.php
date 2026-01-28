<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Bank KPIコマンド共通サービス
 */
abstract class BankCommandService
{
    public function __construct(
        private BankS3Service $bankS3Service,
    ) {
    }

    /**
     * ログを圧縮しS3にアップロードする
     *
     * @param string $env
     * @param CarbonImmutable $executionTime
     * @param string $tempFileName
     * @param string $disk
     * @param string $fileType
     * @return bool
     */
    protected function compressAndUploadToS3(
        string $env,
        CarbonImmutable $executionTime,
        string $tempFileName,
        string $disk,
        string $fileType,
    ): bool {
        // 圧縮処理
        $datetime = $executionTime->format('YmdHis');
        $compressedFileName = sprintf(BankKPIConstant::COMPRESS_FILE_NAME_FORMAT, $env, $datetime);

        // gz圧縮 & 保存
        $tempStorage = Storage::disk(BankKPIConstant::DISK_TEMP);
        $compressStorage = Storage::disk($disk);
        $compressStorage->put($compressedFileName, gzencode($tempStorage->get($tempFileName), 9));
        $tempStorage->delete($tempFileName);

        // S3アップロード用のパスを生成
        $compressedFilePath = $compressStorage->path($compressedFileName);
        $s3Path = $this->bankS3Service->generateS3UploadPath($executionTime, $fileType, $compressedFileName);

        Log::info(sprintf(
            'BankCommandService: S3アップロード開始 path=%s, local_path=%s',
            $s3Path,
            $compressedFilePath
        ));

        // S3にアップロード
        return Storage::disk(BankKPIConstant::DISK_S3)->put(
            $s3Path,
            file_get_contents($compressedFilePath)
        );
    }
}
