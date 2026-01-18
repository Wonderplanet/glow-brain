<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\StageFirstClearReward;
use App\Domain\Resource\Entities\Rewards\StageRandomClearReward;
use App\Domain\Resource\Entities\Rewards\StageSpeedAttackClearReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\Stage\Factories\QuestServiceFactory;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Stage\Services\StageLogService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\StageEndResultData;

class StageEndUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        // Service
        private StageLogService $stageLogService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // Factory
        private QuestServiceFactory $questServiceFactory,
        // Repository
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
        private OprCampaignRepository $oprCampaignRepository,
        private UsrStageSessionRepository $usrStageSessionRepository,
        // Delegator
        private ShopDelegator $shopDelegator,
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
        private InGameDelegator $inGameDelegator,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
    ) {
    }

    /**
     * ステージ終了処理
     *
     * @param CurrentUser $user
     * @param int $platform
     * @param string $mstStageId
     * @param array<mixed> $inGameBattleLog
     * @return StageEndResultData
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        string $mstStageId,
        array $inGameBattleLog = [],
    ): StageEndResultData {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();

        $mstStage = $this->mstStageRepository->getStageGracePeriod($mstStageId, $now, true);
        $mstQuest = $this->mstQuestRepository->getQuestGracePeriod(
            $mstStage->getMstQuestId(),
            $now,
            isThrowError: true,
        );

        $questType = $mstQuest->getQuestType();

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeExp = $beforeUsrUserParameter->getExp();
        $beforeLevel = $beforeUsrUserParameter->getLevel();

        $inGameBattleLogData = $this->stageLogService->makeStageInGameBattleLogData($inGameBattleLog);

        $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);

        $oprCampaigns = $this->oprCampaignRepository
            ->getByIds($usrStageSession->getOprCampaignIds())
            ->keyBy(function ($campaign): string {
                return $campaign->getCampaignType();
            });

        // クエストタイプごとのサービスを取得して処理
        $stageEndQuestService = $this->questServiceFactory->getStageEndQuestService($questType, $mstStageId, $now);
        $stageEndQuestService->end(
            $usrUserId,
            $mstStage,
            $usrStageSession,
            $inGameBattleLogData,
            $oprCampaigns,
            $this->clock->now(),
        );

        // ログ送信
        $this->stageLogService->sendEndLog(
            $usrUserId,
            $mstStageId,
            $usrStageSession->getPartyNo(),
            $inGameBattleLogData,
            $usrStageSession->getAutoLapCount(),
        );

        // 発見した敵情報を保存
        $newUsrEnemyDiscoveries = $this->inGameDelegator->addNewUsrEnemyDiscoveries(
            $usrUserId,
            $inGameBattleLogData->getDiscoveredEnemyDataList(),
            $usrStageSession->getAutoLapCount(),
        );

        // ステージクリアパックの開放
        $usrConditionPacks = $this->shopDelegator->releaseStageClearPack(
            $usrUserId,
            $mstStageId,
            $now
        );

        // 探索報酬が決まるステージIDを更新
        $this->idleIncentiveDelegator->updateRewardMstStageId($usrUserId, $mstStageId, $now);

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
        ) = $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $platform,
            $now,
            $beforeLevel,
            $usrConditionPacks,
        ) {
            // 報酬配布を実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                // レベルアップパックの開放
                $usrConditionPacks = $usrConditionPacks->merge(
                    $this->shopDelegator->releaseUserLevelPack(
                        $usrUserId,
                        $afterUsrUserParameter->getLevel(),
                        $now
                    )
                );
            }

            return [
                $usrConditionPacks,
                $afterUsrUserParameter,
            ];
        });

        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new StageEndResultData(
            $userLevelUpData,
            $this->rewardDelegator->getSentRewards(StageAlwaysClearReward::class),
            $this->rewardDelegator->getSentRewards(StageRandomClearReward::class),
            $this->rewardDelegator->getSentRewards(StageFirstClearReward::class),
            $this->rewardDelegator->getSentRewards(StageSpeedAttackClearReward::class),
            $usrConditionPacks,
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $newUsrEnemyDiscoveries,
            $usrStageSession->getOprCampaignIds()
        );
    }
}
