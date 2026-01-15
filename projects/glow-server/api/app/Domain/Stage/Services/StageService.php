<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Factories\LotteryFactory;
use App\Domain\Gacha\Entities\NoPrizeContent;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\MstInGameSpecialRuleRepository;
use App\Domain\Resource\Mst\Repositories\MstStageEventRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstStageEventSettingRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRewardRepository;
use App\Domain\Stage\Entities\StageStaminaCost;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Enums\StageResetType;
use App\Domain\Stage\Models\IBaseUsrStage;
use App\Domain\Stage\Models\UsrStageEventInterface;
use App\Domain\Stage\Models\UsrStageInterface;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageEnhanceRepository;
use App\Domain\Stage\Repositories\UsrStageEventRepository;
use App\Domain\Stage\Repositories\UsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Http\Responses\Data\UsrStageEnhanceStatusData;
use App\Http\Responses\Data\UsrStageStatusData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageService
{
    public function __construct(
        private MstStageEventSettingRepository $mstStageEventSettingRepository,
        private UsrStageRepository $usrStageRepository,
        private UsrStageEventRepository $usrStageEventRepository,
        private UsrStageSessionRepository $usrStageSessionRepository,
        private UsrStageEnhanceRepository $usrStageEnhanceRepository,
        private LotteryFactory $lotteryFactory,
        private MstStageRewardRepository $mstStageRewardRepository,
        private MstStageEventRewardRepository $mstStageEventRewardRepository,
        private MstInGameSpecialRuleRepository $mstInGameSpecialRuleRepository,
        // Common
        private Clock $clock,
    ) {
    }

    /**
     * ステージを終了できるか検証する
     * @throws GameException
     */
    public function validateCanEnd(
        MstStageEntity $mstStage,
        ?UsrStageSessionInterface $usrStageSession,
        ?UsrStageInterface $usrStage,
        ?UsrStageEventInterface $usrStageEvent,
    ): void {
        if (
            is_null($usrStageSession)
            || !$usrStageSession->isStartedByMstStageId($mstStage->getId())
            || (is_null($usrStage) && is_null($usrStageEvent))
        ) {
            throw new GameException(ErrorCode::STAGE_NOT_START);
        }
    }

    /**
     * スタミナブーストする場合に可能か検証する
     *
     * @param MstStageEntity $mstStage
     * @param IBaseUsrStage|null $usrStage
     * @param bool $isChallengeAd
     * @param int $lapCount
     * @return void
     * @throws GameException
     */
    public function validateCanAutoLap(
        MstStageEntity $mstStage,
        ?IBaseUsrStage $usrStage,
        bool $isChallengeAd,
        int $lapCount,
    ): void {
        // 1回周回扱いの場合は何もしない
        if ($lapCount === 1) {
            return;
        }

        $msg = null;

        if ($lapCount <= 0) {
            // 不正な周回回数
            $msg = "invalid lap count: {$lapCount}";
        } elseif ($isChallengeAd) {
            // 広告視聴時はスタミナブースト不可
            $msg = "cannot auto lap while challenge ad";
        } elseif ($mstStage->getAutoLapType() === null) {
            // ステージがスタミナブースト不可
            $msg = "stage does not support auto lap (mst_stage_id: {$mstStage->getId()})";
        } elseif (
            // ステージクリア条件で未クリア
            $mstStage->getAutoLapType() === StageAutoLapType::AFTER_CLEAR->value
            && (is_null($usrStage) || !$usrStage->isClear())
        ) {
            $msg = "stage is after-clear auto lap but user has not cleared it";
        } elseif ($mstStage->getMaxAutoLapCount() < $lapCount) {
            // 最大周回数超過
            $msg = "lap count exceeds max auto lap count: {$lapCount}";
        }

        if ($msg !== null) {
            throw new GameException(
                ErrorCode::STAGE_CAN_NOT_AUTO_LAP,
                $msg
            );
        }
    }

    /**
     * 消費するスタミナを計算
     *
     * @param MstStageEntity $mstStage
     * @param Collection $oprCampaigns
     * @param int $lapCount
     * @return StageStaminaCost
     */
    public function calcStaminaCost(
        MstStageEntity $mstStage,
        Collection $oprCampaigns,
        int $lapCount,
    ): StageStaminaCost {

        $mstStageCost = $mstStage->getCostStamina();
        /** @var OprCampaignEntity|null $oprCampaign */
        $oprCampaign = $oprCampaigns->filter(function (OprCampaignEntity $campaign) {
            return $campaign->isStaminaCampaign();
        })->first();
        $staminaCostCampaignMultiplier = $oprCampaign?->getStaminaEffectValue() ?? 1;
        $staminaCost = max(
            1,
            (int) floor($mstStageCost * $staminaCostCampaignMultiplier),
        );
        $lapStaminaCost = $staminaCost;
        if ($lapCount > 1) {
            $lapStaminaCost *= $lapCount;
        }

        return new StageStaminaCost(
            $mstStageCost,
            $staminaCost,
            $lapStaminaCost,
            $staminaCostCampaignMultiplier,
            $lapCount,
        );
    }

    /**
     * 該当するキャンペーンがある場合は倍率を適用する
     * @param Collection<string, \App\Domain\Resource\Mst\Entities\OprCampaignEntity> $oprCampaigns
     *   key: App\Domain\Campaign\Enums\CampaignType->value
     * @param string     $rewardType
     * @param int        $targetValue
     * @return int
     */
    public function applyCampaignByRewardType(Collection $oprCampaigns, string $rewardType, int $targetValue): int
    {
        $effectValue = match ($rewardType) {
            RewardType::EXP->value => $oprCampaigns->get(CampaignType::EXP->value)?->getExpEffectValue(),
            RewardType::COIN->value => $oprCampaigns->get(CampaignType::COIN_DROP->value)?->getCoinDropEffectValue(),
            RewardType::ITEM->value => $oprCampaigns->get(CampaignType::ITEM_DROP->value)?->getItemDropEffectValue(),
            default => null,
        };
        if (is_null($effectValue)) {
            return $targetValue;
        }

        return (int) round($targetValue * $effectValue);
    }

    public function getFirstClearRewardsByMstStageId(string $mstStageId, string $questType): Collection
    {
        $rewards = collect();
        if (
            $questType === QuestType::NORMAL->value
            || $questType === QuestType::TUTORIAL->value
        ) {
            $rewards = $this->mstStageRewardRepository->getFirstClearRewardsByMstStageId($mstStageId);
        } elseif ($questType === QuestType::EVENT->value) {
            $rewards = $this->mstStageEventRewardRepository->getFirstClearRewardsByMstStageId($mstStageId);
        }
        return $rewards;
    }

    public function getAlwaysRewardsByMstStageId(string $mstStageId, string $questType): Collection
    {
        $rewards = collect();
        if ($questType === QuestType::NORMAL->value) {
            $rewards = $this->mstStageRewardRepository->getAlwaysRewardsByMstStageId($mstStageId);
        } elseif ($questType === QuestType::EVENT->value) {
            $rewards = $this->mstStageEventRewardRepository->getAlwaysRewardsByMstStageId($mstStageId);
        }
        return $rewards;
    }

    public function getRandomRewardsByMstStageId(string $mstStageId, string $questType): Collection
    {
        $rewards = collect();
        if ($questType === QuestType::NORMAL->value) {
            $rewards = $this->mstStageRewardRepository->getRandomRewardsByMstStageId($mstStageId);
        } elseif ($questType === QuestType::EVENT->value) {
            $rewards = $this->mstStageEventRewardRepository->getRandomRewardsByMstStageId($mstStageId);
        }
        return $rewards;
    }

    public function lotteryPercentageStageReward(Collection $lotteryClearRewards): Collection
    {
        $result = collect();
        if ($lotteryClearRewards->isEmpty()) {
            return $result;
        }
        /** @var \App\Domain\Resource\Mst\Entities\MstStageEventRewardEntity $lotteryClearReward */
        foreach ($lotteryClearRewards as $lotteryClearReward) {
            $dropPercentage = min(100, $lotteryClearReward->getPercentage());
            $lottery = $this->lotteryFactory->createFromMapWithNoPrize(
                weightMap: collect([$lotteryClearReward->getId() => $dropPercentage]),
                contentMap: collect([$lotteryClearReward->getId() => $lotteryClearReward]),
                noPrizeWeight: 100 - $dropPercentage
            );
            $drawResult = $lottery->draw();
            if (!($drawResult instanceof NoPrizeContent)) {
                $result->push($drawResult);
            }
        }
        return $result;
    }

    /**
     * ステージをリタイア/敗北/中断復帰キャンセルする
     * @param string $usrUserId
     * @return UsrStageSessionInterface|null
     */
    public function abort(string $usrUserId): ?UsrStageSessionInterface
    {
        $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);
        // セッションがないなら何もしない
        if (is_null($usrStageSession)) {
            return null;
        }
        $usrStageSession->closeSession();
        $this->usrStageSessionRepository->syncModel($usrStageSession);
        return $usrStageSession;
    }

    public function makeUsrStageStatusData(string $usrUserId, CarbonImmutable $now): UsrStageStatusData
    {
        $usrStageSession = $this->usrStageSessionRepository->get($usrUserId, $now);
        return new UsrStageStatusData($usrStageSession);
    }

    public function resetStageEvent(string $usrUserId, CarbonImmutable $now): void
    {
        $mstStageEventSettings = $this->mstStageEventSettingRepository->getActiveAll($now);
        $dailyResetMstStageIds = $mstStageEventSettings->filter(function ($mstStageEventSetting) {
            return $mstStageEventSetting->getResetType() === StageResetType::DAILY->value;
        })->keys();
        if (count($dailyResetMstStageIds) === 0) {
            return;
        }

        $usrStageEvents = $this->usrStageEventRepository->findByMstStageIds($usrUserId, $dailyResetMstStageIds);
        if ($usrStageEvents->isEmpty()) {
            return;
        }

        // 日跨ぎリセットする
        $updateUsrStageEvents = collect();
        foreach ($usrStageEvents as $usrStageEvent) {
            /** @var UsrStageEventInterface $usrStageEvent */
            $latestResetAt = $usrStageEvent->getLatestResetAt();
            if (!$this->clock->isFirstToday($latestResetAt)) {
                continue;
            }
            $usrStageEvent->reset($now);
            $updateUsrStageEvents->put($usrStageEvent->getMstStageId(), $usrStageEvent);
        }

        $this->usrStageEventRepository->syncModels($updateUsrStageEvents);
    }

    /**
     * スピードアタックのステージのmst_stage_id配列を取得する
     *
     * @param CarbonImmutable $now
     * @return Collection<string>
     */
    private function getSpeedAttackMstStageIds(CarbonImmutable $now): Collection
    {
        return $this->mstInGameSpecialRuleRepository->getByContentTypeAndRuleType(
            InGameContentType::STAGE,
            InGameSpecialRuleType::SPEED_ATTACK,
            $now
        )->map->getTargetId()->unique();
    }

    /**
     * スピードアタックのユーザーモデルをリセットした状態で返す
     *
     * ここではDB更新を実行せず、モデルに変更を加えるだけにとどめる。
     * 不要なDB更新を避けるために、DB更新するかどうかは、このメソッドを使用するユースケース側で判断し実行する。
     *
     * @param CarbonImmutable $now
     * @param Collection<UsrStageEventInterface> $usrStageEvents
     * @return Collection<UsrStageEventInterface>
     */
    public function resetStageEventSpeedAttack(CarbonImmutable $now, Collection $usrStageEvents): Collection
    {
        if ($usrStageEvents->isEmpty()) {
            return collect();
        }

        // スピードアタックのmst_stage_idを取得
        $mstStageIds = $usrStageEvents->map->getMstStageId();
        $speedAttackMstStageIds = $this->getSpeedAttackMstStageIds($now);
        $mstStageIds = $mstStageIds->intersect($speedAttackMstStageIds);
        if ($mstStageIds->isEmpty()) {
            return $usrStageEvents;
        }
        $mstStageIds = $mstStageIds->combine($mstStageIds);

        $mstStageEventSettings = $this->mstStageEventSettingRepository
            ->getActiveByMstStageIds($mstStageIds->keys(), $now)
            ->keyBy->getMstStageId();

        // リセット処理
        foreach ($usrStageEvents as $usrStageEvent) {
            if (!$mstStageIds->has($usrStageEvent->getMstStageId())) {
                // スピードアタックではないのでスキップ
                continue;
            }

            $mstStageEventSetting = $mstStageEventSettings->get($usrStageEvent->getMstStageId());
            if (is_null($mstStageEventSetting)) {
                // 期間外で無効なステージなのでスキップ
                continue;
            }

            // 復刻開催時のリセット処理
            if (
                $this->clock->isAfterAt(
                    $usrStageEvent->getLatestEventSettingEndAt(),
                    $mstStageEventSetting->getStartAt(),
                )
            ) {
                $usrStageEvent->resetResetClearTimeMs($mstStageEventSetting->getEndAt());
            }
        }

        return $usrStageEvents;
    }

    /**
     * ユーザーの強化クエストの状態を取得する
     * リセットが必要な場合はリセットした値の状態を返す
     * ここでは、リセットが必要であっても、DBの更新は行わない
     * @return \Illuminate\Support\Collection<\App\Http\Responses\Data\UsrStageEnhanceStatusData>
     */
    public function fetchUsrStageEnhanceStatusDataList(string $usrUserId, CarbonImmutable $now): Collection
    {
        $usrStageEnhances = $this->usrStageEnhanceRepository->getListByUsrUserId($usrUserId);

        $dataList = collect();
        foreach ($usrStageEnhances as $usrStageEnhance) {
            $isNeedReset = $this->clock->isFirstToday(
                $usrStageEnhance->getLatestResetAt(),
            );

            if ($isNeedReset) {
                $usrStageEnhance->reset($now);
            }

            $dataList->push(
                new UsrStageEnhanceStatusData(
                    $usrStageEnhance->getMstStageId(),
                    $usrStageEnhance->getResetChallengeCount(),
                    $usrStageEnhance->getResetAdChallengeCount(),
                    $usrStageEnhance->getMaxScore(),
                )
            );
        }

        return $dataList;
    }

    /**
     * ユーザーがクリア済みのステージIDリストを取得する
     *
     * @return Collection<string> mst_stages.idの配列
     */
    public function getClearedMstStageIds(string $usrUserId): Collection
    {
        return $this->usrStageRepository->getClearList($usrUserId)
            ->map(function (UsrStageInterface $usrStage) {
                return $usrStage->getMstStageId();
            });
    }

    /**
     * スピードアタックコンテンツかどうか判定
     *
     * @return bool true: スピードアタック, false: スピードアタックではない
     */
    public function isSpeedAttack(string $mstStageId, CarbonImmutable $now): bool
    {
        $mstInGameSpecialRules = $this->mstInGameSpecialRuleRepository
            ->getByContentTypeAndTargetIdAndRuleType(
                InGameContentType::STAGE,
                $mstStageId,
                InGameSpecialRuleType::SPEED_ATTACK,
                $now,
            );

        return $mstInGameSpecialRules->isNotEmpty();
    }
}
