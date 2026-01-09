<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Entities;

/**
 * BOXガチャ抽選ログ用の賞品集計データ
 */
class BoxGachaDrawPrizeLog
{
    public function __construct(
        private string $mstBoxGachaPrizeId,
        private int $drawCount,
    ) {
    }

    public function getMstBoxGachaPrizeId(): string
    {
        return $this->mstBoxGachaPrizeId;
    }

    public function getDrawCount(): int
    {
        return $this->drawCount;
    }

    /**
     * ログ保存用の配列形式に変換
     *
     * @return array{mstBoxGachaPrizeId: string, drawCount: int}
     */
    public function toArray(): array
    {
        return [
            'mstBoxGachaPrizeId' => $this->mstBoxGachaPrizeId,
            'drawCount' => $this->drawCount,
        ];
    }
}
