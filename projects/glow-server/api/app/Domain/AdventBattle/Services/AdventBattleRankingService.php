<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Entities\AdventBattleRankingItem;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\AdventBattle\Services\AdventBattleRewardMaxScoreService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\AdventBattleRaidTotalScoreData;
use App\Http\Responses\Data\AdventBattleRankingData;
use App\Http\Responses\Data\AdventBattleResultData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class AdventBattleRankingService
{
    public function __construct(
        // Repositories
        private MstAdventBattleRepository $mstAdventBattleRepository,
        private UsrAdventBattleRepository $usrAdventBattleRepository,
        // Services
        private AdventBattleCacheService $adventBattleCacheService,
        private AdventBattleRewardRankingService $adventBattleRewardRankingService,
        private AdventBattleRewardRaidTotalScoreService $adventBattleRewardRaidTotalScoreService,
        private AdventBattleRewardMaxScoreService $adventBattleRewardMaxScoreService,
        private MstConfigService $mstConfigService,
        // Delegators
        private UserDelegator $userDelegator,
    ) {
    }

    /**
     * ランキングキャッシュの生成が必要か判定する
     * @param string $mstAdventBattleId
     * @return bool
     */
    private function needGenerateCache(string $mstAdventBattleId): bool
    {
        // キャッシュ切れの確率を低くするためにキャッシュの有効期限が残り60秒以下になったら1/100でキャッシュがあってもキャッシュを再生成する
        // (キャッシュの有効期限が60秒を超えているか、60秒以下で99/100だったらキャッシュを返す)
        $ttl = $this->adventBattleCacheService->getAdventBattleRankingCacheTtl($mstAdventBattleId);
        return ($ttl <= 60 && random_int(1, 100) === 1);
    }

    /**
     * 対象IDの降臨バトルが開催中または終了しているか確認
     * @param string          $mstAdventBattleId
     * @param CarbonImmutable $now
     * @return void
     * @throws GameException
     */
    private function validateAdventBattleActiveOrFinished(
        string $mstAdventBattleId,
        CarbonImmutable $now
    ): void {
        $mstAdventBattle = $this->mstAdventBattleRepository->getByIdWithError($mstAdventBattleId);
        if ($now->lt($mstAdventBattle->getStartAt())) {
            // まだ開催前なのでエラー
            throw new GameException(
                ErrorCode::ADVENT_BATTLE_RANKING_OUT_PERIOD,
                sprintf(
                    'mst_advent_battles record is not found or out of period. (mst_advent_battle_id: %s)',
                    $mstAdventBattleId
                )
            );
        }
    }

    public function getRanking(
        string $usrUserId,
        string $mstAdventBattleId,
        CarbonImmutable $now
    ): AdventBattleRankingData {
        // 降臨バトルの有効確認
        $this->validateAdventBattleActiveOrFinished($mstAdventBattleId, $now);

        $usrAdventBattle = $this->usrAdventBattleRepository->findByMstAdventBattleId(
            $usrUserId,
            $mstAdventBattleId
        );

        // ランキングキャッシュ確認
        $adventBattleRankingItemDataList = $this->adventBattleCacheService->getAdventBattleRankingCache(
            $mstAdventBattleId
        );
        if (!is_null($adventBattleRankingItemDataList) && !$this->needGenerateCache($mstAdventBattleId)) {
            // キャッシュがある場合は自身のランキング情報を追加して返す
            $adventBattleMyRankingData = $this->adventBattleCacheService->generateAdventBattleMyRankingData(
                $usrAdventBattle
            );
            return new AdventBattleRankingData($adventBattleRankingItemDataList, $adventBattleMyRankingData);
        }

        $usrUserIdScoreMap = $this->adventBattleCacheService->getTopRankedPlayerScoreMap($mstAdventBattleId);

        // 取得したユーザーのusr_profilesを取得
        $usrUserIds = collect(array_keys($usrUserIdScoreMap));
        $usrUserProfiles = $this->userDelegator->getUsrUserProfilesByUsrUserIds($usrUserIds);
        $totalScoreMap = $this->usrAdventBattleRepository->getTotalScoresByUsrUserIds(
            $usrUserIds,
            $mstAdventBattleId
        );

        $adventBattleRankingItemDataList = $this->generateAdventBattleRankingItemDataList(
            $usrUserIdScoreMap,
            $usrUserProfiles,
            $totalScoreMap
        );

        // ランキング情報をキャッシュに登録
        $this->adventBattleCacheService->setAdventBattleRankingCache(
            $mstAdventBattleId,
            $adventBattleRankingItemDataList,
            AdventBattleConstant::RANKING_CACHE_TTL_SECONDS
        );

        // 自分のランキングデータを作る
        $adventBattleMyRankingData = $this->adventBattleCacheService->generateAdventBattleMyRankingData(
            $usrAdventBattle
        );

        return new AdventBattleRankingData($adventBattleRankingItemDataList, $adventBattleMyRankingData);
    }

    /**
     * ランキングデータリストを生成
     * @param array<string, float> $usrUserIdScoreMap usr_user_id => score
     * @param Collection<string, int> $totalScoreMap
     * @return Collection<AdventBattleRankingItem>
     */
    private function generateAdventBattleRankingItemDataList(
        array $usrUserIdScoreMap,
        Collection $usrUserProfiles,
        Collection $totalScoreMap
    ): Collection {
        $adventBattleRankingItemDataList = collect();
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
            $totalScore = $totalScoreMap->get($rankerUsrUserId);
            $adventBattleRankingItemData = new AdventBattleRankingItem(
                $usrUserProfile->getMyId(),
                $rank,
                $usrUserProfile->getName(),
                $usrUserProfile->getMstUnitId(),
                $usrUserProfile->getMstEmblemId(),
                $score,
                $totalScore ?? 0
            );
            $adventBattleRankingItemDataList->push($adventBattleRankingItemData);
            $prevScore = $score;
        }
        return $adventBattleRankingItemDataList;
    }

    /**
     * 降臨バトル結果ダイアログ表示用のデータを取得し表示済みフラグを更新する
     * @param string          $usrUserId
     * @param CarbonImmutable $now
     * @return AdventBattleResultData|null
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getAdventBattleResultData(
        string $usrUserId,
        CarbonImmutable $now,
    ): ?AdventBattleResultData {
        $activeMstAdventBattles = $this->mstAdventBattleRepository->getActiveAll($now);
        if ($activeMstAdventBattles->isNotEmpty()) {
            // 次の降臨バトルが開催されているのでダイアログ表示はなし
            return null;
        }

        $mstAdventBattle = $this->mstAdventBattleRepository->getRecentlyFinishedAdventBattle($now);
        if (is_null($mstAdventBattle)) {
            // 終了している降臨バトルがない
            return null;
        }

        $aggregateHours = $this->mstConfigService->getAdventBattleRankingAggregateHours();
        $endDate = (new CarbonImmutable($mstAdventBattle->getEndAt()))->addHours($aggregateHours);
        if ($now->diffInDays($endDate, false) > AdventBattleConstant::SEASON_REWARD_LIMIT_DAYS) {
            // シーズン報酬がもらえなくなる日数を過ぎている
            return null;
        }

        $mstAdventBattleId = $mstAdventBattle->getId();
        $usrAdventBattle = $this->usrAdventBattleRepository->findByMstAdventBattleId(
            $usrUserId,
            $mstAdventBattleId
        );
        $adventBattleMyRankingData = $this
            ->adventBattleCacheService
            ->generateAdventBattleMyRankingData($usrAdventBattle);
        $rank = $adventBattleMyRankingData->getRank();
        if (is_null($rank) && !$mstAdventBattle->isRaid()) {
            // ランキングに参加していないかつ協力バトルでもない場合は報酬なし
            return null;
        }

        $raidTotalScore = $this->adventBattleCacheService->getRaidTotalScore($mstAdventBattleId);
        return new AdventBattleResultData($mstAdventBattleId, $adventBattleMyRankingData, $raidTotalScore);
    }

    /**
     * 降臨バトル報酬を計算
     * @param MstAdventBattleEntity    $mstAdventBattle
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param int|null                 $rank
     * @return Collection<\App\Domain\Resource\Entities\Rewards\AdventBattleReward>
     */
    public function calcAdventBattleRewards(
        MstAdventBattleEntity $mstAdventBattle,
        UsrAdventBattleInterface $usrAdventBattle,
        ?int $rank
    ): Collection {
        $adventBattleRewards = collect();
        if (!is_null($rank)) {
            // 順位報酬
            $adventBattleRewards = $adventBattleRewards->concat(
                $this->adventBattleRewardRankingService->convertRewards(
                    $mstAdventBattle->getId(),
                    $this->adventBattleRewardRankingService->fetchAvailableRewards(
                        $usrAdventBattle
                    )->getMstAdventBattleRewards()
                )
            );
        }

        if ($mstAdventBattle->isRaid()) {
            // 協力バトル参加ユーザー累計ダメージ報酬
            $adventBattleReceivableReward = $this->adventBattleRewardRaidTotalScoreService->fetchAvailableRewards(
                $usrAdventBattle
            );
            $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();
            if ($mstAdventBattleRewards->isNotEmpty()) {
                $this->adventBattleRewardRaidTotalScoreService->setLatestReceivedRewardGroupId(
                    $usrAdventBattle,
                    $adventBattleReceivableReward->getLatestMstAdventBattleRewardGroupId(),
                );
                $adventBattleRewards = $adventBattleRewards->concat(
                    $this->adventBattleRewardRaidTotalScoreService->convertRewards(
                        $mstAdventBattle->getId(),
                        $mstAdventBattleRewards
                    )
                );
            }
        }

        // 受け取ってないハイスコア報酬
        $adventBattleRewards = $adventBattleRewards->concat(
            $this->adventBattleRewardMaxScoreService->convertRewards(
                $mstAdventBattle->getId(),
                $this->adventBattleRewardMaxScoreService->fetchAvailableRewards(
                    $usrAdventBattle
                )->getMstAdventBattleRewards()
            )
        );

        return $adventBattleRewards;
    }

    /**
     * 報酬受取可能な降臨バトルの報酬を取得
     * @param string          $usrUserId
     * @param CarbonImmutable $now
     * @return Collection
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getReceivableRewards(string $usrUserId, CarbonImmutable $now): Collection
    {
        // 報酬受取可能期間(30日+集計期間以内に終了している)な降臨バトルを取得
        $aggregateHours = $this->mstConfigService->getAdventBattleRankingAggregateHours();
        $mstAdventBattles = $this->mstAdventBattleRepository->getWithinRewardReceivePeriod($now, $aggregateHours);
        if ($mstAdventBattles->isEmpty()) {
            // 報酬受取可能期間内の降臨バトルがない
            return collect();
        }

        $usrAdventBattles = $this
            ->usrAdventBattleRepository
            ->findByMstAdventBattleIds($usrUserId, $mstAdventBattles->keys())
            ->filter(function (UsrAdventBattleInterface $usrAdventBattle) {
                return !$usrAdventBattle->isRankingRewardReceived();
            });
        if ($usrAdventBattles->isEmpty()) {
            // 報酬受取可能な降臨バトルがない
            return collect();
        }

        $adventBattleRewards = collect();
        foreach ($usrAdventBattles as $usrAdventBattle) {
            // 受け取り済みフラグの更新
            /** @var UsrAdventBattleInterface $usrAdventBattle */

            $mstAdventBattleId = $usrAdventBattle->getMstAdventBattleId();
            $mstAdventBattle = $mstAdventBattles->get($mstAdventBattleId);

            $adventBattleMyRankingData = $this->adventBattleCacheService->generateAdventBattleMyRankingData(
                $usrAdventBattle
            );
            $rank = $adventBattleMyRankingData->getRank();
            if (is_null($rank) && !$mstAdventBattle->isRaid()) {
                // ランキングに参加していないかつ協力バトルでもない場合は報酬なし
                $usrAdventBattle->setIsRankingRewardReceived(true);
                $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
                continue;
            }
            $adventBattleRewards = $adventBattleRewards->concat(
                $this->calcAdventBattleRewards($mstAdventBattle, $usrAdventBattle, $rank)
            );
            $usrAdventBattle->setIsRankingRewardReceived(true);
            $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
        }

        return $adventBattleRewards;
    }

    /**
     * 開催中の協力バトルがあれば累計ダメージデータを取得
     * @param CarbonImmutable $now
     * @return AdventBattleRaidTotalScoreData|null
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getRaidTotalScoreData(CarbonImmutable $now): ?AdventBattleRaidTotalScoreData
    {
        $mstAdventBattleRaid = $this
            ->mstAdventBattleRepository
            ->getActiveAll($now)
            ->filter(function ($mstAdventBattle) {
                return $mstAdventBattle->isRaid();
            })->first();

        if (is_null($mstAdventBattleRaid)) {
            return null;
        }

        $totalDamage = $this->adventBattleCacheService->getRaidTotalScore($mstAdventBattleRaid->getId());
        return new AdventBattleRaidTotalScoreData($mstAdventBattleRaid->getId(), $totalDamage);
    }
}
