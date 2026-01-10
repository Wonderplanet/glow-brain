<?php

declare(strict_types=1);

namespace App\Services;
use App\Entities\S3BucketObjectEntity;
use App\Entities\S3ObjectPromotionEntity;
use App\Models\Adm\AdmS3BucketScope;
use App\Operators\CloudFrontOperator;
use App\Operators\S3Operator;
use App\Models\Adm\AdmS3Object;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\NotificationTrait;
use App\Utils\StringUtil;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class AdmS3ObjectService
{
    use DatabaseTransactionTrait;
    use NotificationTrait;

    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
        private SendApiService $sendApiService,
        private CloudFrontOperator $cloudFrontOperator,
    ) {
    }

    /**
     * S3からアセット情報を取得してadm_s3_objectsテーブルに同期する
     *
     * @param string|null $uploadAdmUserId アップロードした管理ツールユーザーID
     */
    public function syncFromS3(): void
    {
        $result = [];

        // アップロードユーザーIDが指定されていない場合は現在のユーザーIDを使用
        $uploadAdmUserId = Auth::id();

        $bucketScopeEntities = AdmS3BucketScope::getS3BucketScopeEntities();

        $admS3ObjectArrayList = [];

        // 管理から削除するハッシュリスト
        $removeKeyHashes = AdmS3Object::getBucketKeyHashes();

        // まずS3から全オブジェクト情報を収集
        foreach ($bucketScopeEntities as $entity) {
            $bucket = $entity->getBucket();
            $prefixes = $entity->getPrefixes();
            $result[$bucket] = [];

            foreach ($prefixes as $prefix) {
                $contentsList = $this->s3Operator->getContentsList($bucket, $prefix);

                foreach ($contentsList as $content) {
                    $key = $content['Key'];
                    $size = isset($content['Size']) ? $content['Size'] : 0;
                    $etag = trim($content['ETag'], '"');
                    $lastModified = CarbonImmutable::parse($content['LastModified']);

                    $object = $this->s3Operator->getFile($bucket, $key);
                    $contentType = $object['ContentType'] ?? 'application/octet-stream';

                    $admS3ObjectArrayList[] = AdmS3Object::makeInsertArray(
                        $bucket,
                        $key,
                        (int) $size,
                        $etag,
                        $contentType,
                        (string) $uploadAdmUserId,
                        $lastModified
                    );

                    $bucketKeyHash = AdmS3Object::makeBucketKeyHash($bucket, $key);
                    if (isset($removeKeyHashes[$bucketKeyHash])) {
                        // 同期対象なので削除リストから除外
                        unset($removeKeyHashes[$bucketKeyHash]);
                    }

                    $result[$bucket][] = $key;
                }
            }
        }

        // DB更新処理を一括で実行（upsertを利用）
        if (!empty($admS3ObjectArrayList)) {
            $this->transaction(function () use ($admS3ObjectArrayList, $removeKeyHashes) {
                $chunkSize = 1000;

                // 同期されたオブジェクトの管理データを登録
                foreach (array_chunk($admS3ObjectArrayList, $chunkSize) as $admS3ObjectArrayChunk) {
                    AdmS3Object::saveModels($admS3ObjectArrayChunk);
                }

                // 同期されなかったオブジェクトの管理データを削除
                foreach (array_chunk($removeKeyHashes, $chunkSize) as $removeKeyHashChunk) {
                    AdmS3Object::deleteByBucketKeyHashes(
                        array_values($removeKeyHashChunk)
                    );
                }
            });
        }

        $totalAssets = 0;
        foreach ($result as $bucket => $paths) {
            $totalAssets += count($paths);
        }

        $this->sendProcessCompletedNotification(
            'S3アセット同期完了',
            "合計{$totalAssets}件のアセット情報を同期しました。",
        );
    }

    /**
     * 複数のアセットファイルをS3にアップロードし、DB登録も行う
     * @param string $bucket アップロード先のバケット名
     * @param string $prefix アップロード先のプレフィックス（ディレクトリパス）
     * @param array<string> $fileNames アップロードするファイル名の配列（tempディレクトリに一時保存されたファイル）
     */
    public function uploadAssetsToS3(
        string $bucket,
        string $prefix,
        array $fileNames,
    ): void {

        // ユーザーID取得
        $uploadAdmUserId = Auth::id();
        $uploadedFiles = [];
        $admS3ObjectInsertData = [];

        foreach ($fileNames as $fileName) {
            // tempディレクトリから実際のファイルパスを取得
            $tempPath = Storage::disk('public')->path($fileName);

            // ファイル情報を取得
            $fileInfo = pathinfo($fileName);
            $baseName = $fileInfo['basename'] ?? '';
            $extension = $fileInfo['extension'] ?? '';

            $key = StringUtil::joinPath(
                $prefix,
                $baseName,
            );

            // ファイルのMIMEタイプを取得
            $contentType = $this->getContentType($extension);

            // S3にアップロード（S3Operatorのメソッドを使用）
            try {
                $this->s3Operator->putFromFile($tempPath, $key, $bucket);
                $uploadedFiles[] = $key;

                // ファイルの情報を取得（サイズ）
                $size = filesize($tempPath);

                // S3からアップロードしたファイルの情報を取得
                $objectInfo = $this->s3Operator->getFile($bucket, $key);
                $etag = trim($objectInfo['ETag'] ?? '', '"');
                $lastModified = CarbonImmutable::now();

                $admS3ObjectInsertData[] = AdmS3Object::makeInsertArray(
                    $bucket,
                    $key,
                    (int) $size,
                    $etag,
                    $contentType,
                    (string) $uploadAdmUserId,
                    $lastModified
                );
            } catch (\Exception $e) {
                // アップロード失敗の場合はスキップして次へ
                continue;
            } finally {
                // 一時ファイルを削除
                Storage::disk('public')->delete($fileName);
            }
        }

        // DB登録処理を一括で実行
        if (!empty($admS3ObjectInsertData)) {
            AdmS3Object::saveModels($admS3ObjectInsertData);

            // CloudFrontキャッシュを削除（アップロードされたファイルのパス）
            try {
                $this->cloudFrontOperator->deleteCacheByBucket($bucket, $uploadedFiles);
            } catch (\Exception $e) {
                Log::warning('CloudFrontキャッシュの削除に失敗しました', [
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $this->sendProcessCompletedNotification(
            'アップロード完了',
            "合計" . count($uploadedFiles) . "件のファイルをアップロードしました。",
        );
    }

    /**
     * ファイル拡張子からContent-Type（MIME タイプ）を取得
     * @param string $extension ファイル拡張子
     * @return string Content-Type
     */
    private function getContentType(string $extension): string
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }





    public function getUploadAssetsAction(): Action
    {
        return Action::make('upload_assets')
            ->label('アップロード')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                Select::make('adm_s3_bucket_scope_id')
                    ->label('アップロード先')
                    ->options(function () {
                        return AdmS3BucketScope::getBucketScopeOptions();
                    })
                    ->required(),
                FileUpload::make('upload_files')
                    ->label('画像ファイル')
                    ->multiple()
                    ->acceptedFileTypes(['image/png', 'image/jpeg'])
                    ->maxSize(5120) // 5MB
                    ->directory('temp-uploads')
                    ->preserveFilenames() // アップロード前のファイル名を保持したままアップロードする
                    ->required()
            ])
            ->action(function (array $data) {
                $admS3BucketScope = AdmS3BucketScope::getById($data['adm_s3_bucket_scope_id'] ?? '');
                if ($admS3BucketScope === null) {
                    $this->sendDangerNotification(
                        '無効なS3フォルダ',
                        '指定されたS3フォルダが見つかりません。',
                    );
                    return;
                }

                $this->uploadAssetsToS3(
                    $admS3BucketScope->bucket,
                    $admS3BucketScope->prefix,
                    $data['upload_files']
                );
            });
    }

    public function getSyncAdmS3ObjectFromS3Action(): Action
    {
        return Action::make('sync_adm_s3_object_from_s3')
            ->label('S3アセット同期')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->action(function () {
                $this->syncFromS3();
            })
            ->requiresConfirmation()
            ->modalHeading('S3アセット同期')
            ->modalDescription('S3からアセット情報を取得し、データベースへ同期します。よろしいですか？')
            ->modalSubmitActionLabel('同期を実行');
    }

    public function getAddS3BucketScopeAction(): Action
    {
        return Action::make('add_s3_bucket_scope')
            ->label('S3フォルダ追加')
            ->icon('heroicon-o-plus')
            ->color('info')
            ->form([
                Select::make('bucket')
                    ->label('アップロード先')
                    ->options(function () {
                        return $this->getSelectableBuckets();
                    })
                    ->required(),
                TextInput::make('prefix')
                    ->label('フォルダパス')
                    ->required()
                    ->placeholder('例: gachabanner'),
                TextInput::make('memo')
                    ->label('管理用メモ')
                    ->placeholder('例: ガチャバナー'),
            ])
            ->action(function (array $data) {
                AdmS3BucketScope::addBucketPrefix(
                    $data['bucket'],
                    $data['prefix'],
                    $data['memo'] ?? ''
                );

                $this->sendProcessCompletedNotification(
                    'S3フォルダ追加完了',
                    "バケット「{$data['bucket']}」のフォルダ「{$data['prefix']}」を追加しました。",
                );
            })
            ->requiresConfirmation()
            ->modalHeading('S3フォルダ追加')
            ->modalDescription('新しいS3フォルダを管理対象に追加します。')
            ->modalSubmitActionLabel('追加');
    }

    private function getSelectableBuckets(): array
    {
        return [
            $this->configGetService->getS3BannerBucket() => 'バナー',
        ];
    }

    /**
     * 環境とタグの指定で、S3アセットデータを環境間コピーする
     *
     * @param string $environment コピー元環境名
     * @param string $admPromotionTagId タグID
     */
    public function import(string $environment, string $admPromotionTagId): void
    {
        try {
            // DBテーブルデータをコピー
            $s3ObjectPromotionEntity = $this->importS3Objects($environment, $admPromotionTagId);
            if ($s3ObjectPromotionEntity === null || $s3ObjectPromotionEntity->isEmpty()) {
                Log::info('コピーするS3アセットのデータがありませんでした', [
                    'environment' => $environment,
                    'admPromotionTagId' => $admPromotionTagId,
                ]);

                $this->sendProcessCompletedNotification(
                    'コピーするS3アセットのデータがありませんでした',
                    "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
                );

                // コピー対象がないので終了
                return;
            }

            // CloudFrontキャッシュを削除（コピーされたアセットのパス）
            $this->deleteCloudFrontCacheByAdmS3Objects($s3ObjectPromotionEntity->getAdmS3Objects());

            Log::info('S3アセットのコピーが完了しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
            ]);

            $this->sendProcessCompletedNotification(
                'S3アセットのコピーが完了しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}",
            );
        } catch (\Exception $e) {
            Log::error('S3アセットのコピーに失敗しました', [
                'environment' => $environment,
                'admPromotionTagId' => $admPromotionTagId,
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'S3アセットのコピーに失敗しました',
                "コピー元環境: {$environment}, タグ: {$admPromotionTagId}, " . $e->getMessage(),
            );
            throw $e;
        }
    }

    /**
     * コピー対象のS3アセットをコピー元環境のバケットからコピーする
     *
     * @param string $fromEnv コピー元環境名
     * @param S3ObjectPromotionEntity $s3ObjectPromotionEntity コピー元から来た昇格データをまとめたentity
     * @return Collection<S3BucketObjectEntity> コピーされたS3オブジェクトのエンティティコレクション
     */
    public function copyAssetsBetweenEnvs(
        string $fromEnv,
        S3ObjectPromotionEntity $s3ObjectPromotionEntity,
    ): Collection {
        $admS3Objects = $s3ObjectPromotionEntity->getAdmS3Objects();

        $bucketKeysMap = [];

        // 各種アセットのパス情報を取得
        foreach ($admS3Objects as $admS3Object) {
            $key = $admS3Object->key;
            $bucket = $admS3Object->bucket;

            if (StringUtil::isNotSpecified($key) || StringUtil::isNotSpecified($bucket)) {
                continue;
            }

            $bucketKeysMap[$bucket][$key] = $key;
        }

        // バケット間コピーの実行
        $copiedS3BucketObjectEntities = collect();
        foreach ($bucketKeysMap as $fromBucket => $keys) {
            $toBucket = $this->configGetService->getBucketBySourceEnvAndBucket(
                $fromEnv,
                $fromBucket
            );
            if ($toBucket === null) {
                Log::warning('指定された環境のバケットが見つかりません', [
                    'environment' => $fromEnv,
                    'bucket' => $fromBucket,
                ]);
                $this->sendDangerNotification(
                    '指定された環境のバケットが見つかりません',
                    "環境: {$fromEnv}, バケット: {$fromBucket}"
                );
                continue;
            }

            $copiedS3BucketObjects = $this->s3Operator->copyObjectsBetweenBuckets(
                $fromEnv,
                $fromBucket,
                $toBucket,
                $keys
            );
            $copiedS3BucketObjectEntities = $copiedS3BucketObjectEntities->concat($copiedS3BucketObjects);
        }

        return $copiedS3BucketObjectEntities;
    }

    /**
     * 指定されたS3オブジェクトが属するバケットスコープを取得する
     *
     * @return Collection<AdmS3BucketScope>
     */
    private function getRelatedAdmS3BucketScopes(Collection $admS3Objects): Collection
    {
        // S3オブジェクトから使用されているバケット＋プレフィックスの組み合わせを取得
        $bucketPrefixPairs = $admS3Objects->mapWithKeys(function (AdmS3Object $admS3Object) {
            $bucket = $admS3Object->bucket;
            $prefix = $admS3Object->object_directory;

            return [
                "{$bucket}/{$prefix}" => [
                    'bucket' => $bucket,
                    'prefix' => $prefix,
                ],
            ];
        });

        if ($bucketPrefixPairs->isEmpty()) {
            return collect();
        }

        $query = AdmS3BucketScope::query();
        foreach ($bucketPrefixPairs as $bucketPrefixPair) {
            $query->orWhere(function (Builder $q) use ($bucketPrefixPair) {
                $q->where('bucket', $bucketPrefixPair['bucket'])
                    ->where('prefix', $bucketPrefixPair['prefix']);
            });
        }

        return $query->get();
    }

    public function getS3ObjectPromotionData(string $admPromotionTagId): array
    {
        $admS3Objects = AdmS3Object::query()
            ->where('adm_promotion_tag_id', $admPromotionTagId)
            ->get();

        if ($admS3Objects->isEmpty()) {
            return [];
        }

        // 昇格対象のS3オブジェクトに関連するバケットスコープを取得
        $admS3BucketScopes = $this->getRelatedAdmS3BucketScopes($admS3Objects);

        $s3ObjectPromotionEntity = new S3ObjectPromotionEntity($admS3Objects, $admS3BucketScopes);

        return $s3ObjectPromotionEntity->formatToResponse();
    }

    public function getS3ObjectPromotionDataFromEnvironment(
        string $environment,
        string $admPromotionTagId,
    ): ?S3ObjectPromotionEntity {
        $domain = $this->configGetService->getAdminApiDomain($environment);
        if ($domain === null) {
            return null;
        }

        $endPoint = "get-s3object-promotion-data/$admPromotionTagId";
        $response = $this->sendApiService->sendApiRequest($domain, $endPoint);

        $s3ObjectPromotionEntity = S3ObjectPromotionEntity::createFromResponseArray($response);

        if ($s3ObjectPromotionEntity->isEmpty()) {
            return null;
        }
        return $s3ObjectPromotionEntity;
    }

    private function importS3Objects(string $environment, string $admPromotionTagId): ?S3ObjectPromotionEntity
    {
        $s3ObjectPromotionEntity = $this->getS3ObjectPromotionDataFromEnvironment($environment, $admPromotionTagId);
        if ($s3ObjectPromotionEntity === null || $s3ObjectPromotionEntity->isEmpty()) {
            return null;
        }

        $copiedS3BucketObjectEntities = $this->copyAssetsBetweenEnvs($environment, $s3ObjectPromotionEntity);

        $this->importAdmS3BucketScopes($environment, $s3ObjectPromotionEntity);

        // S3上のオブジェクトの実態と合わせるために、アセットをアップロードした後に、S3アセット同期を実行して、状態を合わせる
        // 昇格元のadm_s3_objectsデータから直接登録しない
        // ここでは、昇格タグの変更はしない
        $this->syncFromS3();

        // 昇格タグを変更
        AdmS3Object::updateAdmPromotionTagId(
            $admPromotionTagId,
            $s3ObjectPromotionEntity->getAdmS3Objects()->pluck('key')
        );

        return $s3ObjectPromotionEntity;
    }

    /**
     * AdmS3Objectコレクションからバケット別にパスをグループ化してCloudFrontキャッシュを削除する
     *
     * @param Collection<AdmS3Object> $admS3Objects 対象のAdmS3Objectコレクション
     */
    private function deleteCloudFrontCacheByAdmS3Objects(Collection $admS3Objects): void
    {
        try {
            // バケット別にパスをグループ化
            $bucketPaths = $admS3Objects->groupBy('bucket')
                ->map(function ($objects) {
                    return $objects->pluck('key')->toArray();
                });

            // 各バケットに対してキャッシュ削除を実行
            foreach ($bucketPaths as $bucket => $paths) {
                $this->cloudFrontOperator->deleteCacheByBucket($bucket, $paths);
            }
        } catch (\Exception $e) {
            Log::warning('CloudFrontキャッシュの削除に失敗しました', [
                'exception' => $e->getMessage(),
            ]);
            $this->sendDangerNotification(
                'CloudFrontキャッシュの削除に失敗しました',
                $e->getMessage(),
            );
        }
    }

    /**
     * 指定されたS3オブジェクトをS3とDBから削除する
     *
     * @param Collection<AdmS3Object> $admS3Objects 削除対象のAdmS3Objectコレクション
     */
    public function deleteS3Objects(Collection $admS3Objects): void
    {
        $deletedCount = 0;
        $failedCount = 0;
        $deleteIds = [];

        // S3からオブジェクトを削除
        foreach ($admS3Objects as $admS3Object) {
            try {
                $this->s3Operator->deleteFile($admS3Object->bucket, $admS3Object->key);
                $deleteIds[] = $admS3Object->id;
                $deletedCount++;
            } catch (\Exception $e) {
                Log::error('S3オブジェクトの削除に失敗しました', [
                    'bucket' => $admS3Object->bucket,
                    'key' => $admS3Object->key,
                    'exception' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        // DBから一括削除（S3削除が成功したもののみ）
        if (!empty($deleteIds)) {
            AdmS3Object::whereIn('id', $deleteIds)->delete();

            // CloudFrontキャッシュを削除（削除されたオブジェクトのパス）
            $deletedAdmS3Objects = $admS3Objects->whereIn('id', $deleteIds);
            $this->deleteCloudFrontCacheByAdmS3Objects($deletedAdmS3Objects);
        }

        if ($failedCount > 0) {
            $this->sendDangerNotification(
                'アセット削除完了（一部失敗）',
                "成功: {$deletedCount}件、失敗: {$failedCount}件"
            );
        } else {
            $this->sendProcessCompletedNotification(
                'アセット削除完了',
                "合計{$deletedCount}件のアセットを削除しました。"
            );
        }
    }

    /**
     * バケットスコープデータのインポート処理。
     * バケットスコープを昇格先のバケットに変換してから登録する。
     */
    private function importAdmS3BucketScopes(
        string $sourceEnv,
        S3ObjectPromotionEntity $s3ObjectPromotionEntity,
    ): void {
        $admS3BucketScopes = $s3ObjectPromotionEntity->getAdmS3BucketScopes();
        if ($admS3BucketScopes->isEmpty()) {
            return;
        }

        foreach ($admS3BucketScopes as $admS3BucketScope) {
            $selfEnvBucket = $this->configGetService->getBucketBySourceEnvAndBucket(
                $sourceEnv,
                $admS3BucketScope->bucket,
            );
            if ($selfEnvBucket === null) {
                Log::warning('指定された環境のバケットが見つかりません', [
                    'environment' => $sourceEnv,
                    'bucket' => $admS3BucketScope->bucket,
                ]);
                continue;
            }
            $admS3BucketScope->changeBucket($selfEnvBucket);
        }

        AdmS3BucketScope::upsert(
            $admS3BucketScopes->map(function (AdmS3BucketScope $admS3BucketScope) {
                return $admS3BucketScope->formatToInsertArray();
            })->all(),
            ['bucket', 'prefix'] // bucket + prefix の組み合わせでユニーク
        );
    }
}
