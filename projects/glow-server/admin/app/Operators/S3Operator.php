<?php

namespace App\Operators;

use App\Services\AwsCredentialProvider;
use App\Services\ConfigGetService;
use App\Traits\NotificationTrait;
use App\Utils\StringUtil;
use Aws\S3\S3Client;
use Aws\CommandPool;
use Aws\Sts\StsClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class S3Operator
{
    use NotificationTrait;

    public function __construct(
        private ConfigGetService $configGetService
    ) {
    }

    // TODO: ファイルアップロードや削除などの操作が成功しているかどうかまで確認するように対応し、エラーハンドリングを追加する

    public function uploadDirectory(string $dirPath, string $destinationPathPrefix = '', $contentType = null): void
    {
        // $dirPath以下の全ファイル
        $files = File::allFiles($dirPath);

        $options = [];
        if (isset($contentType)) {
            // 明示的に指定しないと、暗号化したファイルが想定外の形式（application/x-dosexecなど）になることがあった
            $options['ContentType'] = $contentType;
        }

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            // $dirPathからの相対パス
            $destinationFilePath = $destinationPathPrefix . $file->getRelativePathname();

            // ファイルを S3 にアップロード
            Storage::disk('s3')->put($destinationFilePath, file_get_contents($file->getRealPath()), $options);
        }
    }

    /**
     * @param string $filePath
     * @param string $key キー（プリフィクス含む）
     * @param string $toBucket
     * @return void
     */
    public function putFromFile(string $filePath, string $key, string $toBucket): void
    {
        // TODO: 操作したいバケットごとに読み込むconfigファイルを変えるようにする
        $config = config('filesystems.disks.s3');
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
        ]);

        try {
            $client->putObject([
                'Bucket' => $toBucket,
                'Key' => $key,
                'SourceFile' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('S3 putObject failed', [
                'bucket' => $toBucket,
                'key' => $key,
                'filePath' => $filePath,
                'error' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'S3ファイルアップロード失敗',
                "バケット: {$toBucket}, キー: {$key}, ファイルパス: {$filePath}, エラー: {$e->getMessage()}"
            );
            throw $e;
        }
    }

    /**
     * configを指定してS3にファイルをアップロードする
     *
     * @param array<mixed> $config
     * @param string $filePath ローカルファイルパス
     * @param string $key S3上のパス
     * @return void
     */
    public function putFromFileWithConfig(array $config, string $filePath, string $key): void
    {
        $client = $this->getClient($config);

        $toBucket = $config['bucket'] ?? null;
        if (StringUtil::isNotSpecified($toBucket)) {
            throw new \Exception('S3Operator: bucket is not set in config');
        }

        try {
            $client->putObject([
                'Bucket' => $toBucket,
                'Key' => $key,
                'SourceFile' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('S3 putObject failed', [
                'bucket' => $toBucket,
                'key' => $key,
                'filePath' => $filePath,
                'error' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'S3ファイルアップロード失敗',
                "バケット: {$toBucket}, キー: {$key}, ファイルパス: {$filePath}, エラー: {$e->getMessage()}"
            );
            throw $e;
        }
    }

    /**
     * configを指定してS3クライアントを生成する
     * configにkey, secretが指定されていない場合はECS/EC2ロールを使用する
     * @param array $config
     * @return S3Client
     */
    private function getClient(array $config): S3Client
    {
        $credentials = [
            'key' => $config['key'] ?? null,
            'secret' => $config['secret'] ?? null,
        ];
        if (is_null($credentials['key']) || is_null($credentials['secret'])) {
            $credentials = AwsCredentialProvider::getRoleBasedCredentialProvider();
        }

        return new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => $credentials,
        ]);
    }

    public function getFileWithConfig(array $config, string $bucketName, string $key): ?\Aws\Result
    {
        $client = $this->getClient($config);
        try {
            return $client->getObject([
                'Bucket' => $bucketName,
                'Key' => $key,
            ]);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                return null;
            }
            throw $e;
        }
    }

    /**
     * @param string $bucketName
     * @param string $key キー（プリフィクス含む）
     * @return \Aws\Result
     */
    public function getFile(string $bucketName, string $key): \Aws\Result
    {
        $config = config('filesystems.disks.s3');
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
        ]);

        try {
            return $client->getObject([
                'Bucket' => $bucketName,
                'Key' => $key,
            ]);
        } catch (\Exception $e) {
            Log::error('S3 getObject failed', [
                'bucket' => $bucketName,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'S3ファイル取得失敗',
                "バケット: {$bucketName}, キー: {$key}, エラー: {$e->getMessage()}"
            );
            throw $e;
        }
    }


    /**
     * @param string $bucket
     * @param string $file
     * @return void
     */
    public function deleteFile(string $bucket, string $file): void
    {
        $config = config('filesystems.disks.s3');
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
        ]);

        $client->deleteObject([
            'Bucket' => $bucket,
            'Key' => $file
        ]);
    }

    /**
     * S3 上にあるファイルの一覧を取得。
     * フォルダは除外する。
     *
     * @param string $bucket バケット
     * @param string $prefix プレフィックス
     * @return Collection<array>
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#listobjectsv2
     */
    public function getContentsList(string $bucket, string $prefix = ''): Collection
    {
        // TODO: 右記を考慮した実装にする：S3 の listObjectsV2 は一度に最大1,000件までしか返しません。大量のオブジェクトを扱う場合は、IsTruncated フラグと NextContinuationToken を使って繰り返し取得する必要があります。
        $client = $this->makeS3Client();
        $listObjects = $client->listObjectsV2([
            'Bucket' => $bucket,
            'Prefix' => $prefix,
        ]);
        return collect($listObjects[ 'Contents' ])
            // フォルダは除外する
            ->filter(function ($obj) {
                return !str_ends_with($obj['Key'], '/')
                    && $obj['Size'] > 0;
            });
    }


    /**
     * S3クライアントを生成する（ECS/EC2ロール優先）
     *
     * @return S3Client
     */
    private function makeS3Client(): S3Client
    {
        $config = config('filesystems.disks.s3');
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => AwsCredentialProvider::getRoleBasedCredentialProvider(),
        ]);
        return $client;
    }

    /**
     * 昇格(promotion)時に使用するS3クライアントを生成する
     * @return S3Client
     */
    private function createPromotionS3Client(
        string $fromEnv,
    ): S3Client {
        $config = $this->configGetService->getS3Source();

        $needAssumeRole = !$this->configGetService->isSameAwsAccount($fromEnv);
        if ($needAssumeRole) {
            $roleArn = $config['role_arn'] ?? null;
            if (StringUtil::isNotSpecified($roleArn)) {
                throw new \Exception('S3Operator: role_arn is not set in config');
            }

            // awsアカウントが違うのでassumeRoleする
            $stsClient = new StsClient([
                'version' => 'latest',
                'region' => $config['region'],
                'credentials' => AwsCredentialProvider::getRoleBasedCredentialProvider(),
            ]);

            $result = $stsClient->assumeRole([
                'RoleArn' => $roleArn,
                'RoleSessionName' => 'AdminS3OperatorCreatePromotionS3Client',
                // 'DurationSeconds' => 3600, // STSの一時認証の期限設定。必要になったら指定する。
            ]);
            $credentials = $result['Credentials'];
            $credentials = [
                'key' => $credentials['AccessKeyId'],
                'secret' => $credentials['SecretAccessKey'],
                'token' => $credentials['SessionToken'],
            ];
        } else {
            // awsアカウントが同じなのでアタッチされたロールのポリシーを使う
            $credentials = AwsCredentialProvider::getRoleBasedCredentialProvider();
        }

        return new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => $credentials,
        ]);
    }

    /**
     * @param string $key キー（プリフィクス含む） バケット内のフォルダ
     * @param string $toBucket バケット
     * @param array $uploadFiles アップロードするファイル
     * @return void
     */
    public function parallelMultipleFileUpload(string $key, string $toBucket, array $uploadFiles): void
    {
        $config = config('filesystems.disks.s3_asset');
        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
        ]);

        $commands = [];
        foreach ($uploadFiles as $uploadFile) {

            //AssetBundles以降のディレクトリ構造を保つよう
            $uplodeFileForder = str_replace(config('admin.clientAssetDir') . '/', '', $uploadFile);
            //putObject用のコマンドを生成して配列に格納
            $commands[] = $client->getCommand('PutObject', [
                'Bucket' => $toBucket,
                'Key' => $key . $uplodeFileForder,
                'SourceFile' => $uploadFile,
            ]);
        }

        //コマンド実行　画像をS3にアップロード
        CommandPool::batch($client, $commands);

    }

    /**
     * disk名を指定してS3にテキストをファイルとしてアップロードする
     * @param string $diskName
     * @param string $path
     * @param string $contents
     * @param array  $options
     * @return void
     * @throws \Exception
     */
    public function put(string $diskName, string $path, string $contents, array $options = []): void
    {
        $isSuccess = Storage::disk($diskName)->put($path, $contents, $options);
        if (!$isSuccess) {
            throw new \Exception("s3のputに失敗しました : diskName=$diskName, path=$path");
        }
    }

    /**
     * 指定されたオブジェクトパスの配列に基づいて、fromバケットからtoバケットへ複数オブジェクトをコピーする
     * すでに同じEtagのオブジェクトがtoバケットに存在する場合はコピーしない
     *
     * @param string $fromEnv 昇格元環境名
     * @param string $fromBucket コピー元バケット名
     * @param string $toBucket コピー先バケット名
     * @param array<string> $objectPaths コピー対象のオブジェクトパスの配列
     * @return array<string> コピーに成功したオブジェクトパスの配列
     * @throws \Exception コピー処理中にエラーが発生した場合
     */
    public function copyObjectsBetweenBuckets(string $fromEnv, string $fromBucket, string $toBucket, array $objectPaths): array
    {
        $fromEnvRoleClient = $this->createPromotionS3Client($fromEnv);

        $config = $this->configGetService->getS3Source();
        $toEnvRoleClient = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => AwsCredentialProvider::getRoleBasedCredentialProvider(),
        ]);
        $copiedObjects = [];

        try {
            foreach ($objectPaths as $objectPath) {
                // オブジェクトが存在するか確認し、Etag取得
                try {
                    $fromObj = $fromEnvRoleClient->headObject([
                        'Bucket' => $fromBucket,
                        'Key' => $objectPath,
                    ]);
                    $fromEtag = $fromObj['ETag'] ?? null;
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    // オブジェクトが存在しない場合はスキップ
                    if ($e->getStatusCode() === 404) {
                        Log::warning('コピー対象のオブジェクトが存在しません', [
                            'fromBucket' => $fromBucket,
                            'objectPath' => $objectPath,
                        ]);
                        continue;
                    }
                    throw $e;
                }

                // コピー先のEtag取得（存在しない場合はnull）
                try {
                    $toObj = $fromEnvRoleClient->headObject([
                        'Bucket' => $toBucket,
                        'Key' => $objectPath,
                    ]);
                    $toEtag = $toObj['ETag'] ?? null;
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    $toEtag = null;
                }

                // Etagが同じならコピー不要
                if ($fromEtag !== null && $fromEtag === $toEtag) {
                    continue;
                }

                // コピー元からオブジェクト取得
                try {
                    $fromObject = $fromEnvRoleClient->getObject([
                        'Bucket' => $fromBucket,
                        'Key' => $objectPath,
                    ]);
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    Log::error('オブジェクトの取得に失敗しました', [
                        'fromBucket' => $fromBucket,
                        'objectPath' => $objectPath,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }

                // コピー先にオブジェクトをアップロード
                try {
                    $toEnvRoleClient->putObject([
                        'Bucket' => $toBucket,
                        'Key' => $objectPath,
                        'Body' => $fromObject['Body'],
                        'ContentType' => $fromObject['ContentType'] ?? null,
                        'Metadata' => $fromObject['Metadata'] ?? [],
                    ]);
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    Log::error('オブジェクトのアップロードに失敗しました', [
                        'toBucket' => $toBucket,
                        'objectPath' => $objectPath,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }

                $copiedObjects[] = $objectPath;
            }

            return $copiedObjects;
        } catch (\Exception $e) {
            Log::error('オブジェクトのコピーに失敗しました', [
                'fromBucket' => $fromBucket,
                'toBucket' => $toBucket,
                'error' => $e->getMessage(),
            ]);

            $this->sendDangerNotification(
                "バケット間コピーに失敗しました",
                "fromBucket: {$fromBucket}, toBucket: {$toBucket}, error: {$e->getMessage()}"
            );
            return $copiedObjects;
        }
    }
}
