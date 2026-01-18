<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;
use App\Domain\Common\Entities\Clock;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Http\Responses\Data\UsrAdventBattleStatusData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AdventBattleService
{
    public function __construct(
        private readonly Clock $clock,
        // Repository
        protected MstAdventBattleRepository $mstAdventBattleRepository,
        protected UsrAdventBattleRepository $usrAdventBattleRepository,
        protected UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
        // Service
        // Delegator
    ) {
    }

    /**
     * 対象のユーザー降臨バトル情報の日跨ぎリセット処理を行う
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param CarbonImmutable $now
     * @return bool
     */
    public function resetUsrAdventBattle(UsrAdventBattleInterface $usrAdventBattle, CarbonImmutable $now): bool
    {
        $isNeedReset = $this->clock->isFirstToday(
            $usrAdventBattle->getLatestResetAt(),
        );

        if (!$isNeedReset) {
            return false;
        }

        $usrAdventBattle->reset($now);
        return true;
    }

    /**
     * 期間中の有効なユーザー降臨バトル情報を取得
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return Collection<UsrAdventBattleInterface>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getActiveUsrAdventBattles(string $usrUserId, CarbonImmutable $now): Collection
    {
        $mstAdventBattles = $this->mstAdventBattleRepository->getActiveAll($now);
        if ($mstAdventBattles->isEmpty()) {
            return collect();
        }

        $mstAdventBattleIds = $mstAdventBattles->keys();
        return $this->usrAdventBattleRepository->findByMstAdventBattleIds($usrUserId, $mstAdventBattleIds);
    }

    /**
     * 期間中の有効なユーザー降臨バトル情報の日跨ぎリセットする
     * @param string $usrUserId
     * @param string $mstAdventBattleId
     * @param CarbonImmutable $now
     * @return UsrAdventBattleInterface
     */
    public function fetchAndResetAdventBattleByAdventBattleId(
        string $usrUserId,
        string $mstAdventBattleId,
        CarbonImmutable $now,
    ): UsrAdventBattleInterface {
        $usrAdventBattle = $this->usrAdventBattleRepository->findByMstAdventBattleId($usrUserId, $mstAdventBattleId);
        if (is_null($usrAdventBattle)) {
            return $this->usrAdventBattleRepository->create($usrUserId, $mstAdventBattleId, $now);
        }

        if ($this->resetUsrAdventBattle($usrAdventBattle, $now)) {
            $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
        }
        return $usrAdventBattle;
    }

    /**
     * 期間中の有効なユーザー降臨バトル情報を日跨ぎリセット処理して返却
     *  ここでは、リセットが必要であっても、DBの更新は行わない
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return Collection<UsrAdventBattleInterface>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function fetchUsrAdventBattleList(string $usrUserId, CarbonImmutable $now): Collection
    {
        $usrAdventBattles = $this->getActiveUsrAdventBattles($usrUserId, $now);
        if ($usrAdventBattles->isEmpty()) {
            return collect();
        }

        // 日跨ぎリセットする
        foreach ($usrAdventBattles as $usrAdventBattle) {
            /** @var UsrAdventBattleInterface $usrAdventBattle */
            $this->resetUsrAdventBattle($usrAdventBattle, $now);
        }

        return $usrAdventBattles;
    }

    /**
     * 降臨バトルのセッションステータス情報取得
     *
     * @param string $usrUserId
     * @return UsrAdventBattleStatusData
     */
    public function makeUsrAdventBattleStatusData(string $usrUserId): UsrAdventBattleStatusData
    {
        $usrAdventBattleSession = $this->usrAdventBattleSessionRepository->findByUsrUserId($usrUserId);
        return new UsrAdventBattleStatusData($usrAdventBattleSession);
    }
}
