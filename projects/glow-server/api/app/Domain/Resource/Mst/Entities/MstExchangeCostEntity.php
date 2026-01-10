<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstExchangeCostEntity
{
    public function __construct(
        private string $id,
        private string $mstExchangeLineupId,
        private string $costType,
        private ?string $costId,
        private int $costAmount,
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

    public function getCostType(): string
    {
        return $this->costType;
    }

    public function getCostId(): ?string
    {
        return $this->costId;
    }

    public function getCostAmount(): int
    {
        return $this->costAmount;
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
            'cost_type' => $this->costType,
            'cost_id' => $this->costId,
            'cost_amount' => $this->costAmount * $tradeCount,
        ];
    }
}
