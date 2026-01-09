<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Delegators;

use App\Domain\Cheat\Services\CheatService;
use App\Domain\Resource\Mst\Entities\MstCheatSettingEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class CheatDelegator
{
    public function __construct(
        private readonly CheatService $cheatService,
    ) {
    }

    /**
     * バトル時間でチート判定を行う
     */
    public function checkBattleTime(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        int $battleTimeSeconds,
    ): ?MstCheatSettingEntity {
        return $this->cheatService->checkBattleTime(
            $usrUserId,
            $targetId,
            $cheatContentType,
            $now,
            $battleTimeSeconds,
        );
    }

    /**
     * 一発の最大ダメージでチート判定を行う
     */
    public function checkMaxDamage(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        int $maxDamage,
    ): ?MstCheatSettingEntity {
        return $this->cheatService->checkMaxDamage(
            $usrUserId,
            $targetId,
            $cheatContentType,
            $now,
            $maxDamage,
        );
    }

    /**
     * バトル前後のステータス不一致でチート判定の準備を行う
     */
    public function initBattleStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        Collection $partyStatuses,
    ): void {
        $this->cheatService->initBattleStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType,
            $partyStatuses,
        );
    }

    /**
     * バトル前後のステータス不一致でチート判定を行う
     */
    public function checkBattleStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $partyStatuses,
    ): ?MstCheatSettingEntity {
        return $this->cheatService->checkBattleStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType,
            $now,
            $partyStatuses,
        );
    }

    /**
     * マスターデータのステータス不一致でチート判定を行う
     */
    public function checkMasterDataStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $partyStatuses,
        int $partyNo,
        ?string $eventBonusGroupId,
    ): ?MstCheatSettingEntity {
        return $this->cheatService->checkMasterDataStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType,
            $now,
            $partyStatuses,
            $partyNo,
            $eventBonusGroupId,
        );
    }

    public function checkOpponentMasterDataStatusMismatch(
        string $usrUserId,
        string $targetId,
        string $cheatContentType,
        CarbonImmutable $now,
        Collection $partyStatuses,
        Collection $cheatCheckUnits,
        Collection $mstUnitEncyclopediaEffectIds
    ): ?MstCheatSettingEntity {
        return $this->cheatService->checkOpponentMasterDataStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType,
            $now,
            $partyStatuses,
            $cheatCheckUnits,
            $mstUnitEncyclopediaEffectIds
        );
    }
}
