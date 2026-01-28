<?php

declare(strict_types=1);

namespace App\Entities;

use App\Models\Adm\AdmS3Object;
use App\Models\Adm\AdmS3BucketScope;
use Illuminate\Support\Collection;

class S3ObjectPromotionEntity
{
    private const KEY_ADM_S3_OBJECTS = 'admS3Objects';
    private const KEY_ADM_S3_BUCKET_SCOPES = 'admS3BucketScopes';

    /**
     * @param Collection<AdmS3Object> $admS3Objects
     * @param Collection<AdmS3BucketScope> $admS3BucketScopes
     */
    public function __construct(
        private Collection $admS3Objects,
        private Collection $admS3BucketScopes,
    ) {
    }

    public function formatToResponse(): array
    {
        return [
            self::KEY_ADM_S3_OBJECTS => $this->admS3Objects
                ->map(fn(AdmS3Object $s3Object) => $s3Object->formatToResponse())
                ->values()
                ->all(),
            self::KEY_ADM_S3_BUCKET_SCOPES => $this->admS3BucketScopes
                ->map(fn(AdmS3BucketScope $admS3BucketScope) => $admS3BucketScope->formatToResponse())
                ->values()
                ->all(),
        ];
    }

    public static function createFromResponseArray(array $response): self
    {
        $admS3Objects = collect($response[self::KEY_ADM_S3_OBJECTS] ?? [])
            ->map(fn($item) => AdmS3Object::createFromResponseArray($item));

        $admS3BucketScopes = collect($response[self::KEY_ADM_S3_BUCKET_SCOPES] ?? [])
            ->map(fn($item) => AdmS3BucketScope::createFromResponseArray($item));

        return new self($admS3Objects, $admS3BucketScopes);
    }

    public function isEmpty(): bool
    {
        return $this->admS3Objects->isEmpty();
    }

    /**
     * @return Collection<AdmS3Object>
     */
    public function getAdmS3Objects(): Collection
    {
        return $this->admS3Objects;
    }

    /**
     * @return Collection<AdmS3BucketScope>
     */
    public function getAdmS3BucketScopes(): Collection
    {
        return $this->admS3BucketScopes;
    }
}
