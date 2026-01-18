<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Entities\LogTriggers\StageChallengeLogTrigger;
use App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\StageFirstClearReward;
use App\Domain\Resource\Entities\Rewards\StageRandomClearReward;
use App\Domain\Resource\Entities\Rewards\StageSpeedAttackClearReward;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\MstStageClearTimeRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Entities\StageInGameBattleLog;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\IBaseUsrStage;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\IUsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageEndQuestService
{
    public function __construct(
        // 抽象
        protected IUsrStageRepository $usrStageRepository,
        // MstRepository
        protected MstStageRepository $mstStageRepository,
        protected MstStageClearTimeRewardRepository $mstStageClearTimeRewardRepository,
        // UsrRepository
        protected UsrStageSessionRepository $usrStageSessionRepository,
        // Service
        protected StageService $stageService,
        protected StageMissionTriggerService $stageMissionTriggerService,
        protected StageLogService $stageLogService,
        // Delegator
        protected UserDelegator $userDelegator,
        protected UnitDelegator $unitDelegator,
        protected RewardDelegator $rewardDelegator,
        protected EncyclopediaDelegator $encyclopediaDelegator,
        protected PartyDelegator $partyDelegator,
        // その他
        protected QuestType $questType,
    ) {
    }

    /**
     * ステージ終了処理
     *
     * @param \Illuminate\Support\Collection<string, \App\Domain\Resource\Mst\Entities\OprCampaignEntity> $oprCampaigns
     *   key: \App\Domain\Campaign\Enums\CampaignType->value
     */
    public function end(
        string $usrUserId,
        MstStageEntity $mstStage,
        UsrStageSessionInterface $usrStageSession,
        StageInGameBattleLog $inGameBattleLogData,
        Collection $oprCampaigns,
        CarbonImmutable $now,
    ): void {
        $mstStageId = $mstStage->getId();
        $lapCount = $usrStageSession->getAutoLapCount();

        $usrStage = $this->usrStageRepository->findByMstStageId($usrUserId, $mstStageId);
        $partyNo = $usrStageSession->getPartyNo();

        $this->validateCanEnd($mstStage, $usrStage, $usrStageSession);

        $this->consumeLapStaminaCost($usrUserId, $mstStage, $now, $oprCampaigns, $lapCount);

        $this->clear($usrUserId, $mstStage, $usrStage, $usrStageSession, $inGameBattleLogData, $partyNo, $lapCount);

        $this->applyLapExtras($usrUserId, $mstStage, $partyNo, $lapCount);

        $this->addMstStageRewards($mstStage, $usrStage, $oprCampaigns, $lapCount);

        $this->acquireArtworkAndArtworkFragments($usrUserId, $mstStage, $oprCampaigns, $lapCount);
    }

    /**
     * ステージ終了できる状態かバリデーション
     */
    public function validateCanEnd(
        MstStageEntity $mstStage,
        ?IBaseUsrStage $usrStage,
        UsrStageSessionInterface $usrStageSession,
    ): void {
        if (
            !$usrStageSession->isStartedByMstStageId($mstStage->getId())
            || is_null($usrStage)
        ) {
            throw new GameException(ErrorCode::STAGE_NOT_START);
        }
    }

    /**
     * スタミナブースト時の追加スタミナを消費する
     *
     * @param string $usrUserId
     * @param MstStageEntity $mstStage
     * @param CarbonImmutable $now
     * @param Collection $oprCampaigns
     * @param int $lapCount
     * @return void
     */
    public function consumeLapStaminaCost(
        string $usrUserId,
        MstStageEntity $mstStage,
        CarbonImmutable $now,
        Collection $oprCampaigns,
        int $lapCount,
    ): void {
        if ($lapCount <= 1) {
            return;
        }

        $stageStaminaCost = $this->stageService->calcStaminaCost(
            $mstStage,
            $oprCampaigns,
            $lapCount,
        );

        $diffStaminaCost = $stageStaminaCost->getLapStaminaCost() - $stageStaminaCost->getStaminaCost();
        $this->userDelegator->consumeStamina(
            $usrUserId,
            $diffStaminaCost,
            $now,
            new StageChallengeLogTrigger($mstStage->getId(), $lapCount),
        );
    }

    /**
     * クリア処理の実行
     */
    public function clear(
        string $usrUserId,
        MstStageEntity $mstStage,
        IBaseUsrStage $usrStage,
        UsrStageSessionInterface $usrStageSession,
        StageInGameBattleLog $stageInGameBattleLog,
        int $partyNo,
        int $lapCount,
    ): void {
        $mstStageId = $mstStage->getId();

        $usrStageSession->closeSession();
        $this->usrStageSessionRepository->syncModel($usrStageSession);

        $usrStage->addClearCount($lapCount);
        $this->usrStageRepository->syncModel($usrStage);

        $isQuestFirstClear = $this->isQuestFirstClear($mstStage, $usrStage);

        // ミッショントリガー送信
        $this->stageMissionTriggerService->sendStageClearTriggers(
            $usrUserId,
            $mstStage,
            $usrStage,
            $stageInGameBattleLog,
            $partyNo,
            $isQuestFirstClear,
            $lapCount,
        );
    }

    /**
     * スタミナブースト時の追加処理を行う
     *
     * @param string $usrUserId
     * @param MstStageEntity $mstStage
     * @param int $partyNo
     * @param int $lapCount
     */
    public function applyLapExtras(
        string $usrUserId,
        MstStageEntity $mstStage,
        int $partyNo,
        int $lapCount,
    ): void {
        if ($lapCount <= 1) {
            return;
        }

        // ユニットの出撃回数更新
        $party = $this->partyDelegator->getParty($usrUserId, $partyNo);
        $this->unitDelegator->addBattleCount($usrUserId, $party->getUsrUnitIds(), $lapCount - 1);

        // ミッショントリガー送信
        $this->stageMissionTriggerService->sendStageStartTriggers(
            $usrUserId,
            $mstStage->getId(),
            $partyNo,
            $lapCount - 1,
        );
    }

    /**
     * ステージの共通報酬を配布リストへ追加する
     *
     * @param MstStageEntity $mstStage
     * @param IBaseUsrStage $usrStage
     * @param Collection<string, \App\Domain\Resource\Mst\Entities\OprCampaignEntity> $oprCampaigns
     *    key: App\Domain\Campaign\Enums\CampaignType->value
     * @param int $lapCount
     */
    public function addMstStageRewards(
        MstStageEntity $mstStage,
        IBaseUsrStage $usrStage,
        Collection $oprCampaigns,
        int $lapCount,
    ): void {
        $sendRewards = collect();

        // デフォルト報酬（経験値、コイン）
        $sendRewards = $sendRewards->merge($this->calcBaseRewards($mstStage, $oprCampaigns, $lapCount));
        // 初回クリア報酬
        if ($usrStage->isFirstClear()) {
            $sendRewards = $sendRewards->merge($this->calcFirstClearRewards(
                $this->stageService->getFirstClearRewardsByMstStageId(
                    $mstStage->getId(),
                    $this->questType->value,
                )
            ));
        }

        $alwaysRewards = $this->stageService->getAlwaysRewardsByMstStageId(
            $mstStage->getId(),
            $this->questType->value,
        );
        $randomRewards = $this->stageService->getRandomRewardsByMstStageId(
            $mstStage->getId(),
            $this->questType->value,
        );
        for ($lapNumber = 1; $lapNumber <= $lapCount; $lapNumber++) {
            // 定常クリア報酬（ラップ数分）
            $sendRewards = $sendRewards->merge($this->calcAlwaysClearRewards(
                $alwaysRewards,
                $oprCampaigns,
                $lapNumber,
            ));

            // ランダムクリア報酬（ラップ数分）
            $sendRewards = $sendRewards->merge($this->calcRandomClearRewards(
                $this->stageService->lotteryPercentageStageReward($randomRewards),
                $oprCampaigns,
                $lapNumber,
            ));
        }
        $this->rewardDelegator->addRewards($sendRewards);
    }

    /**
     * クエストを初クリアしたかどうかを判定する。
     * 同一クエスト内の全ステージを1回以上クリアしている場合に初クリアと判定する。
     * true: 初クリア, false: 初クリアでない
     */
    public function isQuestFirstClear(
        MstStageEntity $clearedMstStage,
        IBaseUsrStage $clearedUsrStage,
    ): bool {
        if ($clearedUsrStage->isFirstClear() === false) {
            // クエスト初クリアは、ステージ初クリア時のみ判定する
            return false;
        }

        $mstStages = $this->mstStageRepository->getByMstQuestId($clearedMstStage->getMstQuestId())
            ->keyBy(function (MstStageEntity $mstStage) {
                return $mstStage->getId();
            });
        $mstStageIds = $mstStages->keys();

        $usrStages = $this->usrStageRepository->findByMstStageIds($clearedUsrStage->getUsrUserId(), $mstStageIds);

        foreach ($mstStages as $mstStage) {
            /** @var IBaseUsrStage|null $usrStage */
            $usrStage = $usrStages->get($mstStage->getId());
            if ($usrStage === null || $usrStage->isClear() === false) {
                // 未クリアのステージがある場合はクエスト初クリアでない
                return false;
            }
        }

        return true;
    }

    /**
     * ステージクリアでドロップする原画と原画のかけらの獲得処理を実行
     *
     * @param string $usrUserId
     * @param MstStageEntity $mstStage
     * @param \Illuminate\Support\Collection<string, \App\Domain\Resource\Mst\Entities\OprCampaignEntity> $oprCampaigns
     *    key: \App\Domain\Campaign\Enums\CampaignType->value
     * @param int $lapCount
     * @throws GameException
     */
    protected function acquireArtworkAndArtworkFragments(
        string $usrUserId,
        MstStageEntity $mstStage,
        Collection $oprCampaigns,
        int $lapCount,
    ): void {
        if ($mstStage->hasMstArtworkFragmentDrop() === false) {
            // 原画のかけらドロップの設定がない場合は何もしない
            return;
        }

        $dropRateMultiplier = $oprCampaigns
            ->get(CampaignType::ARTWORK_FRAGMENT->value)
                ?->getArtworkFragmentEffectValue() ?? 1;

        $this->encyclopediaDelegator->acquireArtworkAndArtworkFragments(
            $usrUserId,
            InGameContentType::STAGE,
            $mstStage->getId(),
            $mstStage->getMstArtworkFragmentDropGroupId(),
            $dropRateMultiplier,
            $lapCount,
        );
    }

    /**
     * ベースの配布する報酬を計算する
     * @param MstStageEntity  $mstStage
     * @param Collection<string, \App\Domain\Resource\Mst\Entities\OprCampaignEntity> $oprCampaigns
     *   key: App\Domain\Campaign\Enums\CampaignType->value
     * @param int $lapCount
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function calcBaseRewards(
        MstStageEntity $mstStage,
        Collection $oprCampaigns,
        int $lapCount,
    ) {
        $sendRewards = collect();
        $exp = $this->stageService->applyCampaignByRewardType(
            $oprCampaigns,
            RewardType::EXP->value,
            $mstStage->getExp()
        );
        $coin = $this->stageService->applyCampaignByRewardType(
            $oprCampaigns,
            RewardType::COIN->value,
            $mstStage->getCoin()
        );

        for ($lapNumber = 1; $lapNumber <= $lapCount; $lapNumber++) {
            $sendRewards->push(
                new StageAlwaysClearReward(
                    RewardType::COIN->value,
                    null,
                    $coin,
                    $mstStage->getId(),
                    $oprCampaigns->get(CampaignType::COIN_DROP->value)?->getEffectValue(),
                    $lapNumber,
                )
            );
            $sendRewards->push(
                new StageAlwaysClearReward(
                    RewardType::EXP->value,
                    null,
                    $exp,
                    $mstStage->getId(),
                    $oprCampaigns->get(CampaignType::EXP->value)?->getEffectValue(),
                    $lapNumber,
                )
            );
        }
        return $sendRewards;
    }

    /**
     * @param Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity> $firstClearRewards
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function calcFirstClearRewards(
        Collection $firstClearRewards,
    ): Collection {
        $sendRewards = collect();
        foreach ($firstClearRewards as $firstClearReward) {
            /** @var \App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity $firstClearReward */
            $sendRewards->push(
                new StageFirstClearReward(
                    $firstClearReward->getResourceType(),
                    $firstClearReward->getResourceId(),
                    $firstClearReward->getResourceAmount(),
                    $firstClearReward->getMstStageId(),
                ),
            );
        }
        return $sendRewards;
    }

    /**
     * 通常報酬を計算する
     * @param Collection<string, \App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity> $clearRewards
     * @param Collection<string, OprCampaignEntity> $oprCampaigns
     * @param int $lapNumber
     *   key: App\Domain\Campaign\Enums\CampaignType->value
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function calcAlwaysClearRewards(
        Collection $clearRewards,
        Collection $oprCampaigns,
        int $lapNumber,
    ): Collection {
        $sendRewards = collect();
        foreach ($clearRewards as $clearReward) {
            /** @var \App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity $clearReward */
            $campaign = $this->selectOprCampaignByResourceType(
                $oprCampaigns,
                $clearReward->getResourceType(),
            );
            $amount = $this->stageService->applyCampaignByRewardType(
                $oprCampaigns,
                $clearReward->getResourceType(),
                $clearReward->getResourceAmount()
            );
            $sendRewards->push(
                new StageAlwaysClearReward(
                    $clearReward->getResourceType(),
                    $clearReward->getResourceId(),
                    $amount,
                    $clearReward->getMstStageId(),
                    $campaign?->getEffectValue(),
                    $lapNumber,
                ),
            );
        }
        return $sendRewards;
    }

    /**
     * @param Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity> $randomClearRewards
     * @param Collection<string, OprCampaignEntity> $oprCampaigns
     * @param int $lapNumber
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function calcRandomClearRewards(
        Collection $randomClearRewards,
        Collection $oprCampaigns,
        int $lapNumber,
    ): Collection {
        $sendRewards = collect();
        foreach ($randomClearRewards as $randomClearReward) {
            /** @var \App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity $randomClearReward */
            $campaign = $this->selectOprCampaignByResourceType(
                $oprCampaigns,
                $randomClearReward->getResourceType(),
            );
            $amount = $this->stageService->applyCampaignByRewardType(
                $oprCampaigns,
                $randomClearReward->getResourceType(),
                $randomClearReward->getResourceAmount()
            );
            $sendRewards->push(
                new StageRandomClearReward(
                    $randomClearReward->getResourceType(),
                    $randomClearReward->getResourceId(),
                    $amount,
                    $randomClearReward->getMstStageId(),
                    $campaign?->getEffectValue(),
                    $lapNumber,
                ),
            );
        }
        return $sendRewards;
    }

    /**
     * OprCampaignEntityのコレクションからリソースタイプにあったキャンペーンを取得する
     * 対象とするキャンペーンは経験値、コインドロップ量、アイテムドロップ量のみ
     *
     * @param Collection $oprCampaigns
     * @param string     $resourceType
     * @return OprCampaignEntity|null
     */
    private function selectOprCampaignByResourceType(
        Collection $oprCampaigns,
        string $resourceType,
    ): ?OprCampaignEntity {
        return match ($resourceType) {
            RewardType::EXP->value => $oprCampaigns->get(CampaignType::EXP->value),
            RewardType::COIN->value => $oprCampaigns->get(CampaignType::COIN_DROP->value),
            RewardType::ITEM->value => $oprCampaigns->get(CampaignType::ITEM_DROP->value),
            default => null,
        };
    }

    /**
     * スピードアタック報酬を配布リストへ追加する
     */
    protected function addMstStageClearTimeRewards(
        string $mstStageId,
        ?int $beforeClearTimeMs,
        int $clearTimeMs,
    ): void {
        if (!is_null($beforeClearTimeMs) && $beforeClearTimeMs <= $clearTimeMs) {
            // ベストタイムが更新されてない場合、処理不要
            return;
        }

        $sendRewards = $this->mstStageClearTimeRewardRepository
            ->getByMstStageId($mstStageId)
            ->filter(function ($entity) use ($beforeClearTimeMs, $clearTimeMs) {
                /** @var \App\Domain\Resource\Mst\Entities\MstStageClearTimeRewardEntity $entity */
                return $clearTimeMs <= $entity->getUpperClearTimeMs() &&
                    (is_null($beforeClearTimeMs) || $beforeClearTimeMs > $entity->getUpperClearTimeMs());
            })->map(function ($entity) {
                /** @var \App\Domain\Resource\Mst\Entities\MstStageClearTimeRewardEntity $entity */
                return new StageSpeedAttackClearReward(
                    $entity->getResourceType(),
                    $entity->getResourceId(),
                    $entity->getResourceAmount(),
                    $entity->getMstStageId(),
                    $entity->getId(),
                );
            });

        $this->rewardDelegator->addRewards($sendRewards);
    }
}
