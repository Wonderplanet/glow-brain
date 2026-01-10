<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Encyclopedia\Repositories\LogEncyclopediaRewardRepository;
use App\Domain\Encyclopedia\Repositories\UsrReceivedUnitEncyclopediaRewardRepository;
use App\Domain\Encyclopedia\Services\EncyclopediaService;
use App\Domain\Resource\Entities\Rewards\UnitEncyclopediaReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\EncyclopediaReceiveRewardResultData;
use Illuminate\Support\Collection;

class EncyclopediaReceiveRewardUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly Clock $clock,
        // Repositories
        private readonly UsrReceivedUnitEncyclopediaRewardRepository $usrReceivedUnitEncyclopediaRewardRepository,
        private readonly LogEncyclopediaRewardRepository $logEncyclopediaRewardRepository,
        // Services
        private readonly EncyclopediaService $encyclopediaService,
        private readonly UsrModelDiffGetService $usrModelDiffGetService,
        // Delegator
        private readonly RewardDelegator $rewardDelegator,
        private readonly ShopDelegator $shopDelegator,
        private readonly UserDelegator $userDelegator,
    ) {
    }

    public function exec(
        CurrentUser $user,
        Collection $unitEncyclopediaRewardIds,
        int $platform
    ): EncyclopediaReceiveRewardResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $beforeExp = $beforeUsrUserParameter->getExp();
        $beforeLevel = $beforeUsrUserParameter->getLevel();

        // 報酬を受け取る
        $this->encyclopediaService->receiveReward(
            $usrUserId,
            $unitEncyclopediaRewardIds,
            $platform,
            $now,
        );

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
        ) = $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now, $beforeLevel) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

            // 図鑑ランク報酬ログ
            $encyclopediaRewards = $this->rewardDelegator->getSentRewards(UnitEncyclopediaReward::class);
            $this->logEncyclopediaRewardRepository->create(
                $usrUserId,
                $encyclopediaRewards,
            );

            // レベルアップパックの開放
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
            $usrConditionPacks = collect();
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
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

        // レスポンスデータを作成
        $usrReceivedUnitEncyclopediaRewards = $this
            ->usrReceivedUnitEncyclopediaRewardRepository
            ->getChangedModels();
        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new EncyclopediaReceiveRewardResultData(
            $usrReceivedUnitEncyclopediaRewards,
            $this->rewardDelegator->getSentRewards(UnitEncyclopediaReward::class),
            $this->makeUsrParameterData($afterUsrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $userLevelUpData,
            $usrConditionPacks,
        );
    }
}
