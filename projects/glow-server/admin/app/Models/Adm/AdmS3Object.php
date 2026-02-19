<?php

namespace App\Models\Adm;

use App\Models\Mst\IAssetImage;
use App\Utils\AssetUtil;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class AdmS3Object extends AdmModel implements IAssetImage
{
    protected $table = 'adm_s3_objects';

    protected $guarded = [];

    // adm_usersとのリレーション（必要に応じて）
    public function uploadAdmUser()
    {
        return $this->hasOne(AdmUser::class, 'id', 'upload_adm_user_id');
    }

    public function getUploadAdmUserNameAttribute(): string
    {
        return $this->uploadAdmUser?->name ?? '';
    }

    public function getObjectDirectoryAttribute(): string
    {
        return pathinfo($this->key, PATHINFO_DIRNAME);
    }

    public function getObjectNameAttribute(): string
    {
        return pathinfo($this->key, PATHINFO_BASENAME);
    }

    public function makeAssetPath(): ?string
    {
        return AssetUtil::makeS3UrlWithCacheBusting($this->bucket, $this->key, $this->etag);
    }

    public function makeBgPath(): ?string
    {
        return null;
    }

    public static function makeInsertArray(
        string $bucket,
        string $key,
        int $size,
        string $etag,
        string $contentType,
        string $uploadAdmUserId,
        CarbonImmutable $lastModified
    ): array {
        return [
            'id' => Str::uuid()->toString(),
            'bucket' => $bucket,
            'key' => $key,
            'bucket_key_hash' => self::makeBucketKeyHash($bucket, $key),
            'size' => $size,
            'etag' => $etag,
            'content_type' => $contentType,
            'upload_adm_user_id' => $uploadAdmUserId,
            'last_modified_at' => $lastModified,
        ];
    }

    public static function makeBucketKeyHash(
        string $bucket,
        string $key,
    ): string {
        return md5($bucket . $key);
    }

    public static function saveModels(array $insertData): void
    {
        AdmS3Object::upsert(
            $insertData,
            ['bucket_key_hash'], // 一意キー
            [
                'size',
                'etag',
                'content_type',
                'upload_adm_user_id',
                'last_modified_at',
            ]
        );
    }

    public static function updateAdmPromotionTagId(
        string $admPromotionTagId,
        Collection $keys,
    ): void {
        AdmS3Object::query()
            ->whereIn('key', $keys)
            ->update(['adm_promotion_tag_id' => $admPromotionTagId]);
    }

    /**
     * @return array<string, string> 取得したbucket_key_hashの配列
     *   key: bucket_key_hash, value: bucket_key_hash
     */
    public static function getBucketKeyHashes(): array
    {
        return AdmS3Object::query()
            ->select('bucket_key_hash')
            ->distinct()
            ->pluck('bucket_key_hash', 'bucket_key_hash')
            ->toArray();
    }

    public static function deleteByBucketKeyHashes(array $bucketKeyHashes): void
    {
        AdmS3Object::query()
            ->whereIn('bucket_key_hash', $bucketKeyHashes)
            ->delete();
    }

    public function formatToResponse(): array
    {
        return $this->toArray();
    }

    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }

    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }
}
