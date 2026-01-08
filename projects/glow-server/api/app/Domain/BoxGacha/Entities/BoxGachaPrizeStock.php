<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Entities;

use App\Domain\Resource\Mst\Entities\MstBoxGachaPrizeEntity;

/**
 * BOXガチャ賞品の在庫情報を管理するエンティティ
 */
class BoxGachaPrizeStock
{
    public function __construct(
        private MstBoxGachaPrizeEntity $prize,
        private int $remainingStock,
    ) {
    }

    public function getPrize(): MstBoxGachaPrizeEntity
    {
        return $this->prize;
    }

    public function getRemainingStock(): int
    {
        return $this->remainingStock;
    }

    public function decrementStock(): void
    {
        $this->remainingStock--;
    }

    public function hasStock(): bool
    {
        return $this->remainingStock > 0;
    }
}
