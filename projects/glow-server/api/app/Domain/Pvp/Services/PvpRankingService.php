<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\PvpRankingItem;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\PvpRankingData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class PvpRankingService
{
    public function __construct(
        private UsrPvpRepository $usrPvpRepository,
        private PvpCacheService $pvpCacheService,
        private MstConfigService $mstConfigService,
        private UserDelegator $userDelegator,
    ) {
    }

    public function getRanking(
        string $usrUserId,
        ?SysPvpSeasonEntity $sysPvpSeason,
        CarbonImmutable $now
    ): PvpRankingData {
        if (is_null($sysPvpSeason)) {
            // シーズンが存在しない場合は空のランキングを返す
            $pvpMyRankingItemData = $this->pvpCacheService->generatePvpMyRankingData(
                null
            );
            return new PvpRankingData(collect(), $pvpMyRankingItemData);
        }

        // ランキング開催状態をチェック
        $this->validatePvpActiveOrFinished($sysPvpSeason, $now);

        // topNのランキングアイテムを取得
        $sysPvpSeasonId = $sysPvpSeason->getId();
        $pvpRankingItemDataList = $this->getTopRankingItems(
            $sysPvpSeasonId,
        );

        // 自身のランキング情報取得
        $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId($usrUserId, $sysPvpSeasonId, false);
        $pvpMyRankingItemData = $this->pvpCacheService->generatePvpMyRankingData(
            $usrPvp
        );

        return new PvpRankingData(
            $pvpRankingItemDataList,
            $pvpMyRankingItemData,
        );
    }

    private function validatePvpActiveOrFinished(SysPvpSeasonEntity $sysPvpSeason, CarbonImmutable $now): void
    {
        if ($now->lt($sysPvpSeason->getStartAt())) {
            // 開始前のエラーを投げる
            throw new GameException(
                ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                'sys_pvp_season is not started yet. sys_pvp_season_id: ' . $sysPvpSeason->getId()
            );
        }
    }

    /**
     * @return Collection<PvpRankingItem>
     */
    private function getTopRankingItems(
        string $sysPvpSeasonId,
    ): Collection {
        if ($this->pvpCacheService->isReplaceRankingWithTmp($sysPvpSeasonId)) {
            // 一時的にランキング表示を制限している場合は空のランキングを返す
            return collect();
        }

        // キャッシュがあればそれを返す
        $pvpRankingItems = $this->pvpCacheService->getPvpRankingCache($sysPvpSeasonId);
        if (! is_null($pvpRankingItems) && $pvpRankingItems->isNotEmpty()) {
            return $pvpRankingItems;
        }

        // mstConfigから表示数を取得するが、上限値で制限する
        $pvpRankingDisplayCount = min(
            PvpConstant::RANKING_DISPLAY_LIMIT,
            $this->mstConfigService->getPvpRankingDisplayCount()
        );
        $usrUserIdScoreMap = $this->pvpCacheService->getTopRankedPlayerScoreMap(
            $sysPvpSeasonId,
            $pvpRankingDisplayCount
        );

        // 取得したユーザーのusr_profilesを取得
        $usrUserIds = collect(array_keys($usrUserIdScoreMap));
        $usrUserProfiles = $this->userDelegator->getUsrUserProfilesByUsrUserIds($usrUserIds);

        $pvpRankingItemDataList = $this->generatePvpRankingItemDataList(
            $usrUserIdScoreMap,
            $usrUserProfiles,
        );

        $this->pvpCacheService->setPvpRankingCache(
            $sysPvpSeasonId,
            $pvpRankingItemDataList,
            PvpConstant::RANKING_CACHE_TTL_SECONDS
        );

        return $pvpRankingItemDataList;
    }

    /**
     * ランキングデータリストを生成
     * @param array<string, float> $usrUserIdScoreMap usr_user_id => score
     * @param Collection<\App\Domain\Resource\Usr\Entities\UsrUserProfileEntity> $usrUserProfiles
     * @return Collection<PvpRankingItem>
     */
    private function generatePvpRankingItemDataList(
        array $usrUserIdScoreMap,
        Collection $usrUserProfiles,
    ): Collection {
        $pvpRankingItemDataList = collect();
        $rank = 0;
        $prevScore = 0;
        $sameScoreCount = 1;
        foreach ($usrUserIdScoreMap as $rankerUsrUserId => $score) {
            // floatになっているのでint型に変換
            $score = (int) $score;

            if ($score !== $prevScore) {
                // 同率スコアのユーザー数分順位を進める
                $rank += $sameScoreCount;
                $sameScoreCount = 1;
            } else {
                $sameScoreCount++;
            }
            /** @var \App\Domain\Resource\Usr\Entities\UsrUserProfileEntity $usrUserProfile */
            $usrUserProfile = $usrUserProfiles->get($rankerUsrUserId);
            $pvpRankingItemData = new PvpRankingItem(
                $usrUserProfile->getMyId(),
                $rank,
                $usrUserProfile->getName(),
                $usrUserProfile->getMstUnitId(),
                $usrUserProfile->getMstEmblemId(),
                $score
            );
            $pvpRankingItemDataList->push($pvpRankingItemData);
            $prevScore = $score;
        }
        return $pvpRankingItemDataList;
    }
}
