<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Resource\Entities\Rewards\PvpRankingReward;
use App\Domain\Resource\Entities\Rewards\PvpRankReward;
use App\Domain\Resource\Entities\Rewards\PvpReward;
use App\Domain\Resource\Entities\Rewards\PvpTotalScoreReward;
use App\Domain\Resource\Mst\Entities\MstPvpRewardEntity;
use App\Domain\Resource\Mst\Repositories\MstPvpRankRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRewardGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRewardRepository;
use App\Http\Responses\Data\PvpPreviousSeasonResultData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PvpRewardService
{
    public function __construct(
        private readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        private readonly UsrPvpRepository $usrPvpRepository,
        private readonly MstPvpRewardGroupRepository $mstPvpRewardGroupRepository,
        private readonly MstPvpRewardRepository $mstPvpRewardRepository,
        private readonly MstPvpRankRepository $mstPvpRankRepository,
        private readonly PvpCacheService $pvpCacheService,
        private readonly PvpService $pvpService,
    ) {
    }

    public function getSeasonResult(
        string $usrUserId,
        string $sysPvpSeasonId,
        ?UsrPvpInterface $lastPlayedUsrPvp,
    ): ?PvpPreviousSeasonResultData {
        if (
            is_null($lastPlayedUsrPvp)
            || $lastPlayedUsrPvp->getSysPvpSeasonId() === $sysPvpSeasonId
        ) {
            return null;
        }

        $lastPlayedSysPvpSeason = $this->sysPvpSeasonRepository->getById(
            $lastPlayedUsrPvp->getSysPvpSeasonId(),
        );
        $previousSysPvpSeason = $this->sysPvpSeasonRepository->getPrevious($sysPvpSeasonId, false);
        if (is_null($previousSysPvpSeason) || is_null($lastPlayedSysPvpSeason)) {
            return null;
        }

        // 前回開催シーズンの場合のみ報酬情報追加してシーズン情報をを返す
        if ($lastPlayedSysPvpSeason->getId() === $previousSysPvpSeason->getId()) {
            $myRanking = $this->pvpCacheService->getMyRanking(
                $usrUserId,
                $lastPlayedSysPvpSeason->getId(),
            );
            // 起こることは想定していないが、念の為受取済みの場合は報酬無しで返す
            if ($lastPlayedUsrPvp->isSeasonRewardReceived()) {
                return new PvpPreviousSeasonResultData(
                    $lastPlayedUsrPvp->getPvpRankClassType(),
                    $lastPlayedUsrPvp->getPvpRankClassLevel(),
                    $lastPlayedUsrPvp->getScore(),
                    $myRanking ?? 0,
                    collect(),
                );
            }
            $rankingRewards = $this->getRankingRewards(
                $lastPlayedSysPvpSeason->getId(),
                $myRanking,
                $lastPlayedUsrPvp->isPlayed(),
            );
            $rankRewards = $this->getRankRewards(
                $lastPlayedSysPvpSeason->getId(),
                $lastPlayedUsrPvp->getPvpRankClassTypeEnum(),
                $lastPlayedUsrPvp->getPvpRankClassLevel(),
            );

            return new PvpPreviousSeasonResultData(
                $lastPlayedUsrPvp->getPvpRankClassType(),
                $lastPlayedUsrPvp->getPvpRankClassLevel(),
                $lastPlayedUsrPvp->getScore(),
                $myRanking ?? 0,
                $rankingRewards->concat($rankRewards),
            );
        } else {
            $countSeasonAfter = $this->sysPvpSeasonRepository->countSeasonsAfter(
                $sysPvpSeasonId,
                $lastPlayedUsrPvp->getSysPvpSeasonId(),
            );

            // 前シーズンから3シーズン以内に復帰している場合は、前シーズン情報を返す
            if ($countSeasonAfter < PvpConstant::INACTIVE_SEASON_LIMIT) {
                $rankClassType = $lastPlayedUsrPvp->getPvpRankClassTypeEnum();
                $rankClassLevel = $lastPlayedUsrPvp->getPvpRankClassLevel();
                [
                    $nextSeasonRankClassType,
                    $nextSeasonRankClassLevel,
                ] = $rankClassType->getLowerWithLevel($countSeasonAfter - 1, $rankClassLevel);
                return new PvpPreviousSeasonResultData(
                    $nextSeasonRankClassType->value,
                    $nextSeasonRankClassLevel,
                    0,
                    0,
                    collect(),
                );
            }
        }

        return null;
    }

    /**
     * 前回開催シーズンより前のシーズンの報酬取得
     * addNewMessages内で呼び出される
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return Collection<string, Collection<PvpReward>> key: sys_pvp_season_id, value: Collection<PvpReward>
     */
    public function getOldSeasonRewards(
        string $usrUserId,
        CarbonImmutable $now,
    ): Collection {
        $currentSysPvpSeasonId = $this->pvpService->getCurrentSeasonId($now);

        // 直近３シーズンのsys_pvp_seasonを取得する
        $sysPvpSeasons = $this->sysPvpSeasonRepository->getPreviousWithCount(
            $currentSysPvpSeasonId,
            PvpConstant::SEASON_CONSIDER_LIMIT
        );

        $sysPvpSeasonIds = $sysPvpSeasons->keys();
        $usrPvps = $this->usrPvpRepository->getBySysPvpSeasonIds($usrUserId, $sysPvpSeasonIds);

        if ($usrPvps->isEmpty()) {
            return collect();
        }

        // 前回シーズンID = アクセス日時でのシーズンIDより小さいIDを降順最大3件取得しているので取得したシーズンの最大ID
        $previousSeasonId = (string)$sysPvpSeasons->keys()->max();

        $results = collect();
        foreach ($usrPvps as $usrPvp) {
            $sysPvpSeason = $sysPvpSeasons->get($usrPvp->getSysPvpSeasonId());
            if (is_null($sysPvpSeason)) {
                continue;
            }

            // 前回シーズン（pvp/topのAPIで獲得）を除外し、まだシーズン報酬を受け取っていない場合のみ報酬を付与
            if ($usrPvp->getSysPvpSeasonId() !== $previousSeasonId && !$usrPvp->isSeasonRewardReceived()) {
                $myRanking = $this->pvpCacheService->getMyRanking(
                    $usrUserId,
                    $sysPvpSeason->getId(),
                );

                $rankingRewards = $this->getRankingRewards(
                    $sysPvpSeason->getId(),
                    $myRanking,
                    $usrPvp->isPlayed(),
                );
                $rankRewards = $this->getRankRewards(
                    $sysPvpSeason->getId(),
                    $usrPvp->getPvpRankClassTypeEnum(),
                    $usrPvp->getPvpRankClassLevel(),
                );

                $results->put(
                    $sysPvpSeason->getId(),
                    $rankingRewards->concat($rankRewards)
                );
            }
        }

        return $results;
    }

    /**
     * 報酬受取済みフラグを更新
     */
    public function markSeasonRewardAsReceived(Collection $lastPlayedUsrPvps): void
    {
        $lastPlayedUsrPvps = $lastPlayedUsrPvps->filter();
        if ($lastPlayedUsrPvps->isEmpty()) {
            return;
        }
        foreach ($lastPlayedUsrPvps as $lastPlayedUsrPvp) {
            if ($lastPlayedUsrPvp->isSeasonRewardReceived()) {
                continue;
            }
            $lastPlayedUsrPvp->setIsSeasonRewardReceived(true);
        }
        $this->usrPvpRepository->syncModels($lastPlayedUsrPvps);
    }

    /**
     * 報酬受取済みフラグを更新
     */
    public function markSeasonRewardAsReceivedBySeasonIds(string $usrUserId, Collection $sysSeasonIds): void
    {
        $usrPvps = $this->usrPvpRepository->getBySysPvpSeasonIds($usrUserId, $sysSeasonIds);
        $this->markSeasonRewardAsReceived($usrPvps);
    }

    /**
     * 付与用の報酬データに変換
     *
     * @param Collection<MstPvpRewardEntity> $mstPvpRewards
     * @return Collection<PvpRankReward>
     */
    private function convertPvpRankRewards(string $sysPvpSeasonId, Collection $mstPvpRewards): Collection
    {
        if ($mstPvpRewards->isEmpty()) {
            return collect();
        }

        return $mstPvpRewards->map(function (MstPvpRewardEntity $mstPvpReward) use ($sysPvpSeasonId) {
            return new PvpRankReward(
                $mstPvpReward->getResourceType(),
                $mstPvpReward->getResourceId(),
                $mstPvpReward->getResourceAmount(),
                $sysPvpSeasonId,
                $mstPvpReward->getMstPvpRewardGroupId(),
            );
        });
    }

    /**
     * 付与用の報酬データに変換
     *
     * @param Collection<MstPvpRewardEntity> $mstPvpRewards
     * @return Collection<PvpRankingReward>
     */
    private function convertPvpRankingRewards(string $sysPvpSeasonId, Collection $mstPvpRewards): Collection
    {
        if ($mstPvpRewards->isEmpty()) {
            return collect();
        }

        return $mstPvpRewards->map(function (MstPvpRewardEntity $mstPvpReward) use ($sysPvpSeasonId) {
            return new PvpRankingReward(
                $mstPvpReward->getResourceType(),
                $mstPvpReward->getResourceId(),
                $mstPvpReward->getResourceAmount(),
                $sysPvpSeasonId,
                $mstPvpReward->getMstPvpRewardGroupId(),
            );
        });
    }

    /**
     * 累計ポイント報酬付与用の報酬データに変換
     *
     * @param Collection<MstPvpRewardEntity> $mstPvpRewards
     * @return Collection<PvpTotalScoreReward>
     */
    private function convertPvpTotalScoreRewards(string $sysPvpSeasonId, Collection $mstPvpRewards): Collection
    {
        if ($mstPvpRewards->isEmpty()) {
            return collect();
        }

        return $mstPvpRewards->map(function (MstPvpRewardEntity $mstPvpReward) use ($sysPvpSeasonId) {
            return new PvpTotalScoreReward(
                $mstPvpReward->getResourceType(),
                $mstPvpReward->getResourceId(),
                $mstPvpReward->getResourceAmount(),
                $sysPvpSeasonId,
                $mstPvpReward->getMstPvpRewardGroupId(),
            );
        });
    }

    private function getRankRewards(
        string $sysPvpSeasonId,
        PvpRankClassType $rankClassType,
        int $rankClassLevel,
    ): Collection {
        $mstPvpRank = $this->mstPvpRankRepository->getByClassTypeAndLevel(
            $rankClassType->value,
            $rankClassLevel,
        );
        if (is_null($mstPvpRank)) {
            return collect();
        }
        $mstPvpRewardGroup = $this->mstPvpRewardGroupRepository->getByRank(
            $sysPvpSeasonId,
            $mstPvpRank->getId(),
        );

        if (is_null($mstPvpRewardGroup)) {
            return collect();
        }

        $mstPvpRewards = $this->mstPvpRewardRepository->getByGroupId(
            $mstPvpRewardGroup->getId(),
        );

        return $this->convertPvpRankRewards($sysPvpSeasonId, $mstPvpRewards);
    }

    private function getRankingRewards(
        string $sysPvpSeasonId,
        ?int $ranking,
        bool $isPlayed,
    ): Collection {
        if ($ranking === null || $isPlayed === false) {
            return collect();
        }
        $mstPvpRewardGroup = $this->mstPvpRewardGroupRepository->getByRanking(
            $sysPvpSeasonId,
            $ranking,
        );

        if (is_null($mstPvpRewardGroup)) {
            return collect();
        }

        $mstPvpRewards = $this->mstPvpRewardRepository->getByGroupId(
            $mstPvpRewardGroup->getId(),
        );

        return $this->convertPvpRankingRewards($sysPvpSeasonId, $mstPvpRewards);
    }

    /**
     * 累計ポイント報酬取得
     *
     * @param string $sysPvpSeasonId
     * @param int $currentScore
     * @param int $maxReceivedScoreReward
     * @return Collection<PvpTotalScoreReward>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getTotalScoreRewards(
        string $sysPvpSeasonId,
        int $currentScore,
        int $maxReceivedScoreReward,
    ): Collection {
        $mstPvpRewardGroups = $this->mstPvpRewardGroupRepository->getByTotalScore(
            $sysPvpSeasonId,
            $currentScore,
            $maxReceivedScoreReward + 1,    // 前回受取済み報酬の次の報酬から取得
        );

        if ($mstPvpRewardGroups->isEmpty()) {
            return collect();
        }

        $mstPvpRewards = $this->mstPvpRewardRepository->getByGroupIds(
            $mstPvpRewardGroups->map(fn ($group) => $group->getId()),
        );
        return $this->convertPvpTotalScoreRewards($sysPvpSeasonId, $mstPvpRewards);
    }
}
