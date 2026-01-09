<?php

namespace App\Entities;

use Illuminate\Support\Collection;

class S3BucketScopeEntity
{
    /**
     * @param string $bucket バケット名
     * @param Collection<int, string> $prefixes プレフィックスCollection
     */
    public function __construct(
        private string $bucket,
        private Collection $prefixes
    ) {
    }

    /**
     * バケット名を取得
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * プレフィックスCollectionを取得
     *
     * @return Collection<int, string>
     */
    public function getPrefixes(): Collection
    {
        return $this->prefixes;
    }
}
