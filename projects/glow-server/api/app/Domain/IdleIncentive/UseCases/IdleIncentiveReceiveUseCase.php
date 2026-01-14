<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\IdleIncentive\Repositories\LogIdleIncentiveRewardRepository;
use App\Domain\IdleIncentive\Repositories\UsrIdleIncentiveRepository;
use App\Domain\IdleIncentive\Services\IdleIncentiveMissionTriggerService;
use App\Domain\IdleIncentive\Services\IdleIncentiveRewardService;
use App\Domain\IdleIncentive\Services\IdleIncentiveService;
use App\Domain\Resource\Entities\Rewards\IdleIncentiveReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Mst\Repositories\MstIdleIncentiveRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\Shop\Delegators\ShopPassEffectDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\IdleIncentiveReceiveResultData;
use Carbon\CarbonImmutable;

class IdleIncentiveReceiveUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private MstIdleIncentiveRepository $mstIdleIncentiveRepository,
        private UsrIdleIncentiveRepository $usrIdleIncentiveRepository,
        private LogIdleIncentiveRewardRepository $logIdleIncentiveRewardRepository,
        // Service
        private IdleIncentiveService $idleIncentiveService,
        private IdleIncentiveRewardService $idleIncentiveRewardService,
        private IdleIncentiveMissionTriggerService $idleIncentiveMissionTriggerService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // Delegator
        private UserDelegator $userDelegator,
        private ShopDelegator $shopDelegator,
        private ShopPassEffectDelegator $shopPassEffectDelegator,
        private RewardDelegator $rewardDelegator,
    ) {
    }

    public function exec(CurrentUser $user, int $platform): IdleIncentiveReceiveResultData
    {
        $now = $this->clock->now();

        $mstIdleIncentive = $this->mstIdleIncentiveRepository->getLast(isThrowError: true);
        $shopPassActiveEffect = $this->shopPassEffectDelegator
            ->getShopPassActiveEffectDataByUsrUserId($user->id, $now);

        // 経過時間の取得
        $usrIdleIncentive = $this->usrIdleIncentiveRepository->findOrCreate($user->id, $now);
        $idleStartedAt = $usrIdleIncentive->getIdleStartedAt();
        $elapsedMinutes = $this->idleIncentiveService->calcElapsedTimeMinutes(
            $mstIdleIncentive,
            CarbonImmutable::parse($idleStartedAt),
            $now,
        );

        $beforeUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
        $beforeExp = $beforeUsrUserParameter->getExp();
        $beforeLevel = $beforeUsrUserParameter->getLevel();

        // 一定時間未満の場合は報酬なし
        $this->idleIncentiveService->validateReceivable($mstIdleIncentive, $elapsedMinutes);

        // 報酬の算出とRewardManagerへの追加
        $rewards = $this->idleIncentiveRewardService->calcRewards(
            $user->id,
            $now,
            $elapsedMinutes,
            IdleIncentiveExecMethod::NORMAL,
            $shopPassActiveEffect->getIdleIncentiveRewardMultiplier(),
        );
        $this->rewardDelegator->addRewards($rewards);

        // 放置収益のユーザーステータスを更新
        $usrIdleIncentive->setIdleStartedAt($now->toDateTimeString());
        $this->usrIdleIncentiveRepository->syncModel($usrIdleIncentive);

        // ミッショントリガー送信
        $this->idleIncentiveMissionTriggerService->sendReceiveTrigger();

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
            $sentIdleIncentiveRewards,
        ) = $this->applyUserTransactionChanges(
            function () use (
                $user,
                $platform,
                $now,
                $beforeLevel,
                $idleStartedAt,
                $elapsedMinutes,
            ) {
                // 報酬を付与
                $this->rewardDelegator->sendRewards($user->id, $platform, $now);

                $usrConditionPacks = collect();
                // 報酬受け取りでレベルが上っている可能性があるので再取得
                $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
                if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                    // レベルアップパックの開放判定
                    $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                        $user->id,
                        $afterUsrUserParameter->getLevel(),
                        $now
                    );
                }

                // 探索報酬ログ保存
                $sentIdleIncentiveRewards = $this->rewardDelegator->getSentRewards(IdleIncentiveReward::class);
                $this->logIdleIncentiveRewardRepository->create(
                    $user->id,
                    IdleIncentiveExecMethod::NORMAL,
                    CarbonImmutable::parse($idleStartedAt),
                    $elapsedMinutes,
                    $sentIdleIncentiveRewards,
                    $now
                );

                return [
                    $usrConditionPacks,
                    $afterUsrUserParameter,
                    $sentIdleIncentiveRewards,
                ];
            }
        );

        // レスポンス用意
        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new IdleIncentiveReceiveResultData(
            $sentIdleIncentiveRewards,
            $userLevelUpData,
            $usrIdleIncentive,
            $this->makeUsrParameterData($afterUsrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $usrConditionPacks,
        );
    }
}
