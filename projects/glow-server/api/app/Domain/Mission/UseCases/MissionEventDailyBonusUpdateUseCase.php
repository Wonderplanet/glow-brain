<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Repositories\UsrMissionEventDailyBonusProgressRepository;
use App\Domain\Mission\Services\MissionEventDailyBonusUpdateService;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\MissionEventDailyBonusUpdateResultData;

class MissionEventDailyBonusUpdateUseCase
{
    use UseCaseTrait;

    public function __construct(
        //services
        private MissionEventDailyBonusUpdateService $missionEventDailyBonusUpdateService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        //repositories
        private UsrMissionEventDailyBonusProgressRepository $usrMissionEventDailyBonusProgressRepository,
        //delegators
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        private ShopDelegator $shopDelegator,
        //other
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user, int $platform): MissionEventDailyBonusUpdateResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
        $beforeExp = $beforeUsrUserParameter->getExp();
        $beforeLevel = $beforeUsrUserParameter->getLevel();

        // トランザクション処理
        list(
            $afterUsrUserParameter,
            $usrConditionPacks,
        ) = $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now, $beforeLevel) {
            // イベントデイリーボーナス進捗更新と報酬自動受け取り
            $this->missionEventDailyBonusUpdateService->updateStatuses(
                $usrUserId,
                $platform,
                $now,
            );

            // レベルアップパックの開放
            $usrConditionPacks = collect();
            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                    $usrUserId,
                    $afterUsrUserParameter->getLevel(),
                    $now
                );
            }

            return [
                $afterUsrUserParameter,
                $usrConditionPacks,
            ];
        });

        // レスポンスデータを作成
        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new MissionEventDailyBonusUpdateResultData(
            $this->rewardDelegator->getSentRewards(MissionEventDailyBonusReward::class),
            $this->usrMissionEventDailyBonusProgressRepository->getChangedModels(),
            $this->makeUsrParameterData($afterUsrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
            $userLevelUpData,
            $usrConditionPacks,
        );
    }
}
