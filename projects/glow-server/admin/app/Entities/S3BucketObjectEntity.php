<?php

declare(strict_types=1);

namespace App\Entities;

/**
 * S3上のオブジェクトを表現するエンティティクラス
 * バケット名とキー（バケット上でのオブジェクトのパス）を保持する
 */
class S3BucketObjectEntity
{
    public function __construct(
        private readonly string $bucket,
        private readonly string $key,
    ) {
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * バケット+キーの組み合わせで一意性を判定
     */
    public function equals(S3BucketObjectEntity $other): bool
    {
        return $this->bucket === $other->bucket && $this->key === $other->key;
    }

    /**
     * 文字列表現でバケット/キーの形式で返す
     */
    public function toString(): string
    {
        return "{$this->bucket}/{$this->key}";
    }
}
