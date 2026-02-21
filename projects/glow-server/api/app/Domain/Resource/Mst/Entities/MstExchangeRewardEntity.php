<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstExchangeRewardEntity
{
    public function __construct(
        private string $id,
        private string $mstExchangeLineupId,
        private string $resourceType,
        private ?string $resourceId,
        private int $resourceAmount,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstExchangeLineupId(): string
    {
        return $this->mstExchangeLineupId;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getResourceAmount(): int
    {
        return $this->resourceAmount;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    /**
     * ログ用の配列を生成
     *
     * @param int $tradeCount 交換回数
     * @return array<string, mixed>
     */
    public function formatToLog(int $tradeCount): array
    {
        return [
            'resource_type' => $this->resourceType,
            'resource_id' => $this->resourceId,
            'resource_amount' => $this->resourceAmount * $tradeCount,
        ];
    }
}
