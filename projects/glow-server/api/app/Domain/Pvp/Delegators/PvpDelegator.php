<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Delegators;

use App\Domain\Pvp\Services\PvpRewardService;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PvpDelegator
{
    public function __construct(
        private PvpService $pvpService,
        private PvpRewardService $pvpRewardService,
    ) {
    }

    /**
     * 現在のPvPシーズンを取得
     */
    public function getCurrentSeason(CarbonImmutable $now, bool $isThrowError = true): ?SysPvpSeasonEntity
    {
        return $this->pvpService->getCurrentSysPvpSeason($now, $isThrowError);
    }

    /**
     * 前回開催シーズンより前のシーズンの報酬をメッセージに付与
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return Collection 報酬データのコレクション
     */
    public function getOldSeasonRewards(
        string $usrUserId,
        CarbonImmutable $now,
    ): Collection {
        return $this->pvpRewardService->getOldSeasonRewards(
            $usrUserId,
            $now,
        );
    }

    /**
     * 最後にプレイしたシーズンの報酬受取済みフラグを更新
     */
    public function markSeasonRewardAsReceivedBySeasonIds(string $usrUserId, Collection $sysPvpSeasonIds): void
    {
        $this->pvpRewardService->markSeasonRewardAsReceivedBySeasonIds($usrUserId, $sysPvpSeasonIds);
    }
}
