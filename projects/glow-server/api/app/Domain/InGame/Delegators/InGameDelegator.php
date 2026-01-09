<?php

declare(strict_types=1);

namespace App\Domain\InGame\Delegators;

use App\Domain\InGame\Services\InGameEnemyService;
use App\Domain\InGame\Services\InGameSpecialRuleService;
use App\Domain\Resource\Entities\Party;
use App\Domain\Resource\Enums\InGameContentType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class InGameDelegator
{
    public function __construct(
        // Service
        private InGameEnemyService $inGameEnemyService,
        private InGameSpecialRuleService $inGameSpecialRuleService,
    ) {
    }

    /**
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\InGameDiscoveredEnemy> $discoveredEnemiesDataList
     * @param int $lapCount
     * @return Collection<\App\Domain\Resource\Usr\Entities\UsrEnemyDiscoveryEntity>
     */
    public function addNewUsrEnemyDiscoveries(
        string $usrUserId,
        Collection $discoveredEnemiesDataList,
        int $lapCount = 1,
    ): Collection {
        return $this->inGameEnemyService
            ->addNewUsrEnemyDiscoveries($usrUserId, $discoveredEnemiesDataList, $lapCount)
            ->map->toEntity();
    }

    public function checkAndGetParty(
        string $usrUserId,
        int $partyNo,
        InGameContentType $inGameContentType,
        string $targetId,
        CarbonImmutable $now,
    ): ?Party {
        return $this->inGameSpecialRuleService->checkAndGetParty(
            $usrUserId,
            $partyNo,
            $inGameContentType,
            $targetId,
            $now,
        );
    }

    /**
     * 敵の図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstEnemyCharacterId
     */
    public function markAsCollected(string $usrUserId, string $mstEnemyCharacterId): void
    {
        $this->inGameEnemyService->markAsCollected($usrUserId, $mstEnemyCharacterId);
    }
}
