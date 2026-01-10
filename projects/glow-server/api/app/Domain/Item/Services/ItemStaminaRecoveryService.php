<?php

declare(strict_types=1);

namespace App\Domain\Item\Services;

use App\Domain\Resource\Entities\LogTriggers\StaminaRecoveryLogTrigger;
use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;

/**
 * スタミナ回復アイテム専用サービス
 */
class ItemStaminaRecoveryService
{
    public function __construct(
        private UsrItemService $usrItemService,
        private UserDelegator $userDelegator,
    ) {
    }

    /**
     * スタミナ回復アイテム（パーセンテージ指定）の使用処理
     */
    public function applyStaminaRecoveryPercent(
        string $usrUserId,
        MstItemEntity $mstItem,
        int $amount,
        CarbonImmutable $now
    ): void {
        $userLimitStaminaPercent = (int) $mstItem->getEffectValue();

        // スタミナ回復実行（バリデーションも含む）
        $this->userDelegator->recoverStaminaByPercent(
            $usrUserId,
            $now,
            $userLimitStaminaPercent,
            $amount,
        );

        // アイテム消費（ログ記録含む）
        $this->usrItemService->consumeItem(
            $usrUserId,
            $mstItem->getId(),
            $amount,
            new StaminaRecoveryLogTrigger(),
        );
    }

    /**
     * スタミナ回復アイテム（固定値指定）の使用処理
     */
    public function applyStaminaRecoveryFixed(
        string $usrUserId,
        MstItemEntity $mstItem,
        int $amount,
        CarbonImmutable $now
    ): void {
        $fixedRecoveryAmount = (int) $mstItem->getEffectValue();

        // スタミナ回復実行（バリデーションも含む）
        $this->userDelegator->recoverStaminaByFixed(
            $usrUserId,
            $now,
            $fixedRecoveryAmount,
            $amount,
        );

        // アイテム消費（ログ記録含む）
        $this->usrItemService->consumeItem(
            $usrUserId,
            $mstItem->getId(),
            $amount,
            new StaminaRecoveryLogTrigger(),
        );
    }
}
