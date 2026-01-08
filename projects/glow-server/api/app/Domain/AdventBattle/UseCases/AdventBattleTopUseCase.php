<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\AdventBattle\Services\AdventBattleLogService;
use App\Domain\AdventBattle\Services\AdventBattleTopService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Entities\Rewards\AdventBattleMaxScoreReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRaidTotalScoreReward;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\AdventBattleTopResultData;

class AdventBattleTopUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Repository
        private readonly MstAdventBattleRepository $mstAdventBattleRepository,
        private readonly UsrAdventBattleRepository $usrAdventBattleRepository,
        // Service
        private readonly UsrModelDiffGetService $usrModelDiffGetService,
        private readonly AdventBattleTopService $adventBattleTopService,
        private readonly AdventBattleLogService $adventBattleLogService,
        // Delegator
        private readonly UserDelegator $userDelegator,
        private readonly RewardDelegator $rewardDelegator,
        // Other
        private readonly Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string $mstAdventBattleId
     * @param int $platform
     * @return AdventBattleTopResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        string $mstAdventBattleId,
        int $platform,
    ): AdventBattleTopResultData {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();

        $mstAdventBattle = $this->mstAdventBattleRepository->getByIdWithError($mstAdventBattleId);
        $usrAdventBattle = $this->usrAdventBattleRepository->findByMstAdventBattleId(
            $usrUserId,
            $mstAdventBattleId,
        );

        $this->adventBattleTopService->addRaidTotalScoreReward(
            $mstAdventBattle,
            $usrAdventBattle,
        );

        $this->adventBattleTopService->addMaxScoreReward(
            $mstAdventBattle,
            $usrAdventBattle,
        );

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use ($usrUserId, $now, $platform) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // 報酬ログ
            $this->adventBattleLogService->sendTopLog($usrUserId);
        });

        // レスポンス用意
        return new AdventBattleTopResultData(
            $this->rewardDelegator->getSentRewards(AdventBattleRaidTotalScoreReward::class),
            $this->rewardDelegator->getSentRewards(AdventBattleMaxScoreReward::class),
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
        );
    }
}
