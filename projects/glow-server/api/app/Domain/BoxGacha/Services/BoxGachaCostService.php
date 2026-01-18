<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Services;

use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\LogTriggers\BoxGachaLogTrigger;
use App\Domain\Resource\Mst\Entities\MstBoxGachaEntity;

/**
 * BOXガチャのコスト消費を管理するサービス
 *
 * 将来的にアイテム以外のリソース（通貨等）での消費が追加される可能性があるため、
 * コスト消費処理を専用クラスとして分離
 */
class BoxGachaCostService
{
    public function __construct(
        private readonly ItemDelegator $itemDelegator,
    ) {
    }

    /**
     * コスト消費に必要な量を持っているかバリデーション
     *
     * @param string $usrUserId
     * @param MstBoxGachaEntity $mstBoxGacha
     * @param int $drawCount
     */
    public function validateCost(
        string $usrUserId,
        MstBoxGachaEntity $mstBoxGacha,
        int $drawCount
    ): void {
        $totalCost = $this->calculateTotalCost($mstBoxGacha, $drawCount);
        $this->itemDelegator->validateItemAmount($usrUserId, $mstBoxGacha->getCostId(), $totalCost);
    }

    /**
     * コストを消費する
     *
     * @param string $usrUserId
     * @param MstBoxGachaEntity $mstBoxGacha
     * @param int $drawCount
     * @param int $boxLevel
     */
    public function consumeCost(
        string $usrUserId,
        MstBoxGachaEntity $mstBoxGacha,
        int $drawCount,
        int $boxLevel,
    ): void {
        $totalCost = $this->calculateTotalCost($mstBoxGacha, $drawCount);
        $logTrigger = new BoxGachaLogTrigger($mstBoxGacha->getId(), $boxLevel);

        $this->itemDelegator->useItemByMstItemId(
            $usrUserId,
            $mstBoxGacha->getCostId(),
            $totalCost,
            $logTrigger
        );
    }

    /**
     * 合計コストを計算
     *
     * @param MstBoxGachaEntity $mstBoxGacha
     * @param int $drawCount
     * @return int
     */
    public function calculateTotalCost(MstBoxGachaEntity $mstBoxGacha, int $drawCount): int
    {
        return $mstBoxGacha->getCostNum() * $drawCount;
    }
}
