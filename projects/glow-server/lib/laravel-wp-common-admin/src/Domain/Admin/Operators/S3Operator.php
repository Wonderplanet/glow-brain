<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Operators;

use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class S3Operator
{
    /**
     * filesystems.phpで定義しているAWS s3のdisksのキー名
     */
    public const CONFIG_NAME_S3 = 's3';

    /**
     * @param string $dirPath
     * @param string $configName
     * @param string $destinationPathPrefix
     * @return void
     * @throws \Exception
     */
    public function uploadDirectory(string $dirPath, string $configName, string $destinationPathPrefix = ""): void
    {
        // $dirPath以下の全ファイル
        $files = File::allFiles($dirPath);

        foreach ($files as $file) {
            if (!$file->isFile()) continue;
            // $dirPathからの相対パス
            $destinationFilePath = $destinationPathPrefix . $file->getRelativePathname();

            // ファイルを S3 にアップロード
            $result = Storage::disk($configName)->put($destinationFilePath, file_get_contents($file->getRealPath()));

            if ($result === false){
                throw new \Exception("{$configName}へのアップロードに失敗しました。");
            }
        }
    }

    /**
     * 指定したディレクトリ内の全ファイルとサイズを取得
     * 1階層のみ対応
     *
     * @param string $config
     * @param string $directory
     *
     * @return array
     */
    public function getAllFilesAndSize(string $config, string $directory): array
    {
        $storage = Storage::disk($config);
        // 全ファイル取得
        $allFiles = $storage->allFiles($directory);

        $result = [];
        // 各ファイルサイズ取得
        foreach ($allFiles as $file) {
            $size = $storage->size($file);
            $result[] = [
                'file' => $file,
                'size' => $size,
            ];
        }
        return $result;
    }

    /**
     * インポート元環境のアセットを自環境のバケットにコピーする
     * @param string $fromConfig
     * @param string $toConfig
     * @param string $directory
     * @return void
     */
    public function copyAsset(string $fromConfig, string $toConfig, string $directory): void
    {
        /** @var AwsS3V3Adapter $fromStorage */
        $fromStorage = Storage::drive($fromConfig);
        /** @var AwsS3V3Adapter $toStorage */
        $toStorage = Storage::drive($toConfig);

        // 対象のファイル名を取得
        $allFiles = $fromStorage->allFiles($directory);
        // 異なるバケット間でファイルコピーを実行
        foreach ($allFiles as $file) {
            $fromStorage->getClient()->copy(
                $fromStorage->getConfig()['bucket'],
                $file,
                $toStorage->getConfig()['bucket'],
                $file,
                null,
            );
        }
    }

    /**
     * インポート元環境のmysqldumpファイルをローカルにダウンロードする
     *
     * @param string $configName
     * @param string $s3FilePath
     * @param string $downloadFilePath
     * @return void
     * @throws \Exception
     */
    public function downloadMasterMySqlDump(string $configName, string $s3FilePath, string $downloadFilePath): void
    {
        if (!$this->existFile($configName, $s3FilePath)) {
            throw new \Exception("File not found on S3. filePath:{$s3FilePath}");
        }

        // S3からファイルを取得して、指定パスに保存
        $fileContent = Storage::disk($configName)->get($s3FilePath);
        $result = Storage::disk('local')->put($downloadFilePath, $fileContent);
        if (!$result) {
            throw new \Exception("Failure File Download . filePath:{$s3FilePath}");
        }
    }

    /**
     * downloadMasterMySqlDumpでダウンロードしたmysqldumpファイルをS3にアップロードする
     * @param string $configName
     * @param string $s3FilePath
     * @param string $downloadFilePath
     * @return void
     * @throws \Exception
     */
    public function uploadDownloadMasterMySqlDump(string $configName, string $s3FilePath, string $downloadFilePath): void
    {
        // S3にファイルをアップロード
        $fileContent = Storage::disk('local')->get($downloadFilePath);
        $result = Storage::disk($configName)->put($s3FilePath, $fileContent);
        if ($result === false) {
            throw new \Exception("Failure File Upload . localFilePath: {$downloadFilePath}, filePath:{$s3FilePath}");
        }
    }

    /**
     * 指定したファイルが存在するかチェック
     *
     * @param string $configName
     * @param string $s3FilePath
     * @return bool
     */
    public function existFile(string $configName, string $s3FilePath): bool
    {
        return Storage::disk($configName)->exists($s3FilePath);
    }

    /**
     * S3からファイルの内容を取得して変数に保持する
     *
     * @param string $configName
     * @param string $s3FilePath
     * @return string|null ファイルの内容
     * @throws \Exception
     */
    public function getFileContent(string $configName, string $s3FilePath): ?string
    {
        if (!$this->existFile($configName, $s3FilePath)) {
            throw new \Exception("File not found on S3. filePath:{$s3FilePath}");
        }

        // S3からファイルを取得して、変数に保持
        $fileContent = Storage::disk($configName)->get($s3FilePath);
        if ($fileContent === false) {
            throw new \Exception("Failure to get file content. filePath:{$s3FilePath}");
        }

        return $fileContent;
    }

    /**
     * インポート元環境のマスターデータファイルを自環境のバケットにコピーする
     *
     * @param string $fromConfig
     * @param string $toConfig
     * @param string $filePath
     * @return void
     */
    public function copyMasterDataFile(string $fromConfig, string $toConfig, string $filePath): void
    {
        /** @var AwsS3V3Adapter $fromStorage */
        $fromStorage = Storage::drive($fromConfig);
        /** @var AwsS3V3Adapter $toStorage */
        $toStorage = Storage::drive($toConfig);

        // 対象ファイル名が存在するかチェック
        if (!$this->existFile($fromConfig, $filePath)) {
            throw new \Exception("File not exist on S3. config:{$fromConfig}, filePath:{$filePath}");
        }

        // 異なるバケット間でファイルコピーを実行
        $fromStorage->getClient()->copy(
            $fromStorage->getConfig()['bucket'],
            $filePath,
            $toStorage->getConfig()['bucket'],
            $filePath,
            null
        );
    }
}
