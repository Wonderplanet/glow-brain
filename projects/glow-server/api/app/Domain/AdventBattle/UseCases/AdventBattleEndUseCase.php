<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Factories\AdventBattleServiceFactory;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;
use App\Domain\AdventBattle\Services\AdventBattleEndCheatService;
use App\Domain\AdventBattle\Services\AdventBattleLogService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Resource\Entities\Rewards\AdventBattleAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleDropReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleFirstClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRandomClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRankReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\AdventBattleEndResultData;

class AdventBattleEndUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private readonly AdventBattleLogService $adventBattleLogService,
        private readonly AdventBattleEndCheatService $adventBattleEndCheatService,
        private readonly UsrModelDiffGetService $usrModelDiffGetService,
        // Repository
        private readonly MstAdventBattleRepository $mstAdventBattleRepository,
        private readonly UsrAdventBattleRepository $usrAdventBattleRepository,
        private readonly UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
        // Delegator
        private readonly RewardDelegator $rewardDelegator,
        private readonly ShopDelegator $shopDelegator,
        private readonly UserDelegator $userDelegator,
        private readonly InGameDelegator $inGameDelegator,
        // Factories
        private readonly AdventBattleServiceFactory $adventBattleServiceFactory,
        // Other
        private readonly Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string $mstAdventBattleId
     * @param int $platform
     * @param array<mixed> $inGameBattleLog
     * @return AdventBattleEndResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        string $mstAdventBattleId,
        int $platform,
        array $inGameBattleLog = [],
    ): AdventBattleEndResultData {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeExp = $beforeUsrUserParameter->getExp();
        $beforeLevel = $beforeUsrUserParameter->getLevel();

        $mstAdventBattle = $this->mstAdventBattleRepository->getActive($mstAdventBattleId, $now, true);
        $usrAdventBattleSession = $this->usrAdventBattleSessionRepository->findWithError($usrUserId, true);
        $usrAdventBattle = $this->usrAdventBattleRepository->findByMstAdventBattleId(
            $usrUserId,
            $mstAdventBattleId,
        );

        $inGameBattleLogData = $this->adventBattleLogService->makeInGameBattleLogData($inGameBattleLog);
        $this->adventBattleEndCheatService->checkCheat(
            $inGameBattleLogData,
            $usrAdventBattle,
            $now,
            $usrAdventBattleSession->getPartyNo(),
            $usrAdventBattleSession->calcBattleTime($now),
            $mstAdventBattle->getEventBonusGroupId(),
        );

        $adventBattleEndService = $this->adventBattleServiceFactory->getAdventBattleEndService(
            $mstAdventBattle->getAdventBattleType()
        );
        // 降臨バトル終了時情報の取得
        $adventBattleEndService->end(
            $usrUserId,
            $mstAdventBattle,
            $usrAdventBattle,
            $usrAdventBattleSession,
            $inGameBattleLogData,
        );

        $allUserTotalScore = $adventBattleEndService->updateAllUserTotalScore(
            $mstAdventBattle,
            $inGameBattleLogData,
        );

        // 発見した敵情報を保存
        $newUsrEnemyDiscoveries = $this->inGameDelegator->addNewUsrEnemyDiscoveries(
            $usrUserId,
            $inGameBattleLogData->getDiscoveredEnemyDataList(),
        );

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
        ) = $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now, $beforeLevel) {
            // 報酬配布を実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // 報酬ログ
            $this->adventBattleLogService->sendEndRewardLog($usrUserId);

            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

            $usrConditionPacks = collect();
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                // レベルアップパックの開放
                $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                    $usrUserId,
                    $afterUsrUserParameter->getLevel(),
                    $now
                );
            }

            return [
                $usrConditionPacks,
                $afterUsrUserParameter,
            ];
        });

        // レスポンス用意
        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new AdventBattleEndResultData(
            $usrAdventBattle,
            $allUserTotalScore,
            $this->makeUsrParameterData(
                $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)
            ),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $userLevelUpData,
            $this->rewardDelegator->getSentRewards(AdventBattleAlwaysClearReward::class),
            $this->rewardDelegator->getSentRewards(AdventBattleRandomClearReward::class),
            $this->rewardDelegator->getSentRewards(AdventBattleFirstClearReward::class),
            $this->rewardDelegator->getSentRewards(AdventBattleDropReward::class),
            $this->rewardDelegator->getSentRewards(AdventBattleRankReward::class),
            $usrConditionPacks,
            $newUsrEnemyDiscoveries,
        );
    }
}
