<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Encyclopedia\Services\EncyclopediaService;
use App\Domain\Resource\Entities\Rewards\EncyclopediaFirstCollectionReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\EncyclopediaReceiveFirstCollectionRewardResultData;

class EncyclopediaReceiveFirstCollectionRewardUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly Clock $clock,
        private readonly UserDelegator $userDelegator,
        private readonly RewardDelegator $rewardDelegator,
        // Services
        private readonly EncyclopediaService $encyclopediaService,
        private readonly UsrModelDiffGetService $usrModelDiffGetService,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $encyclopediaType,
        string $encyclopediaId,
        int $platform
    ): EncyclopediaReceiveFirstCollectionRewardResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // 報酬を受け取る
        $this->encyclopediaService->receiveFirstCollectionReward(
            $usrUserId,
            $encyclopediaType,
            $encyclopediaId,
        );

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $now) {
            // 報酬配布を実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
        });

        // レスポンス用意
        return new EncyclopediaReceiveFirstCollectionRewardResultData(
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrEnemyDiscoveries(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)),
            $this->rewardDelegator->getSentRewards(EncyclopediaFirstCollectionReward::class),
        );
    }
}
