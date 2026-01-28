<?php

namespace App\Models\Adm;

use App\Entities\S3BucketScopeEntity;
use Carbon\CarbonImmutable;

class AdmS3BucketScope extends AdmModel
{
    protected $table = 'adm_s3_bucket_scopes';

    protected $guarded = [];

    /**
     * バケット・プレフィックスを追加
     */
    public static function addBucketPrefix(string $bucket, string $prefix, ?string $memo = null): self
    {
        $model = new self();
        $model->id = $model->newUniqueId();
        $model->bucket = $bucket;
        $model->prefix = $prefix;
        $model->memo = $memo ?? '';
        $model->save();

        return $model;
    }

    /**
     * 指定されたbucket + prefixの組み合わせが既に存在するかチェック
     */
    public static function existsBucketPrefix(string $bucket, string $prefix): bool
    {
        return self::query()
            ->where('bucket', $bucket)
            ->where('prefix', $prefix)
            ->exists();
    }

    /**
     * 昇格時に、昇格元環境のバケットのままではなく、昇格先のバケットに変更する
     */
    public function changeBucket(string $bucket): void
    {
        $this->bucket = $bucket;
    }

    /**
     * プレフィックス（フォルダ）の選択肢を取得
     */
    public static function getPrefixOptions(): array
    {
        return self::select(['prefix', 'memo'])
            ->distinct()
            ->pluck('memo', 'prefix')
            ->map(function ($memo, $prefix) {
                return $memo . ' (' . $prefix . ')';
            })
            ->toArray();
    }

    public static function getBucketScopeOptions(): array
    {
        return self::select(['id', 'memo'])
            ->distinct()
            ->pluck('memo', 'id')
            ->toArray();
    }

    public static function getById(string $id): ?self
    {
        return self::query()
            ->where('id', $id)
            ->first();
    }

    /**
     * S3BucketScopeEntityのコレクションを生成
     *
     * @return array<S3BucketScopeEntity>
     */
    public static function getS3BucketScopeEntities(): array
    {
        $scopes = self::all();
        $groupedByBucket = $scopes->groupBy('bucket');

        $entities = [];
        foreach ($groupedByBucket as $bucket => $models) {
            $prefixes = $models->pluck('prefix');
            $entities[] = new S3BucketScopeEntity($bucket, $prefixes);
        }

        return $entities;
    }

    /**
     * レスポンス用の配列形式にフォーマット
     */
    public function formatToResponse(): array
    {
        return $this->toArray();
    }

    /**
     * レスポンス配列からモデルインスタンスを作成
     */
    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }

    /**
     * インサート用の配列形式にフォーマット
     */
    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }
}
