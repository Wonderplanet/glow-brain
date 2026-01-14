<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Services;

use App\Domain\Common\Services\DeferredTaskService;
use App\Domain\JumpPlus\Models\UsrJumpPlusRewardInterface;
use App\Domain\JumpPlus\Repositories\DynJumpPlusRewardRepository;
use App\Domain\JumpPlus\Repositories\UsrJumpPlusRewardRepository;
use App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity;
use App\Domain\Resource\Entities\JumpPlusRewardBundle;
use App\Domain\Resource\Entities\Rewards\JumpPlusReward;
use App\Domain\Resource\Mng\Repositories\MngJumpPlusRewardBundleRepository;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class JumpPlusRewardService
{
    public function __construct(
        private MngJumpPlusRewardBundleRepository $mngJumpPlusRewardBundleRepository,
        private DynJumpPlusRewardRepository $dynJumpPlusRewardRepository,
        private UsrJumpPlusRewardRepository $usrJumpPlusRewardRepository,
        // Delegators
        private UserDelegator $userDelegator,
        // Services
        private DeferredTaskService $deferredTaskService,
    ) {
    }

    /**
     * 受取可能な報酬情報を取得
     *
     * DynamoDBの報酬情報を確認しつつ、報酬1種類あたり生涯1回の受取り制限を実装
     *
     * @param string $usrUserId
     * @param \Carbon\CarbonImmutable $now
     * @return Collection<\App\Domain\Resource\Entities\JumpPlusRewardBundle>
     */
    public function getReceivableRewards(string $usrUserId, CarbonImmutable $now): Collection
    {
        $usrUser = $this->userDelegator->getUsrUserByUsrUserId($usrUserId);

        // BNID連携していない場合は配布できる報酬はないので空配列を返す
        if ($usrUser->hasBnUserId() === false) {
            return collect();
        }

        $mngJumpPlusRewardBundles = $this->mngJumpPlusRewardBundleRepository->getActiveMngJumpPlusRewardBundles($now);
        if ($mngJumpPlusRewardBundles->isEmpty()) {
            return collect();
        }

        $mngScheduleIds = $mngJumpPlusRewardBundles->keys();

        // 受取済の報酬情報を取得
        $usrRewards = $this->usrJumpPlusRewardRepository->getByMngJumpPlusRewardScheduleIds(
            $usrUserId,
            $mngScheduleIds,
        )->keyBy(function ($usrReward) {
            /** @var UsrJumpPlusRewardInterface $usrReward */
            return $usrReward->getMngJumpPlusRewardScheduleId();
        });

        // 有効な報酬IDから、受取済の報酬IDを除外して、受取可の報酬IDを取得
        $availableMngScheduleIds = $mngScheduleIds->diff($usrRewards->keys());

        if ($availableMngScheduleIds->isEmpty()) {
            return collect();
        }

        // DynamoDBデータを確認して、実際に配布可能な報酬情報を取得
        /**
         * @var Collection<string, DynJumpPlusRewardEntity> $dynJumpPlusRewards
         * key: mngJumpPlusRewardScheduleId
         */
        $dynJumpPlusRewards = $this->dynJumpPlusRewardRepository
            ->getByMngJumpPlusRewardScheduleIds(
                $usrUser->getBnUserId(),
                $availableMngScheduleIds,
            )->reduce(function (Collection $carry, DynJumpPlusRewardEntity $dynJumpPlusReward) {
                if ($dynJumpPlusReward->canReceive()) {
                    $carry->put($dynJumpPlusReward->getMngJumpPlusRewardScheduleId(), $dynJumpPlusReward);
                }
                return $carry;
            }, collect())
            ->only($availableMngScheduleIds->all());

        if ($dynJumpPlusRewards->isEmpty()) {
            return collect();
        }

        $availableMngScheduleIds = $dynJumpPlusRewards->keys();

        // Rewardインスタンスに変換
        $jumpPlusRewardBundles = collect();
        foreach ($availableMngScheduleIds as $mngScheduleId) {
            $mngJumpPlusRewardBundle = $mngJumpPlusRewardBundles->get($mngScheduleId);
            if (is_null($mngJumpPlusRewardBundle)) {
                continue;
            }

            /** @var \App\Domain\Resource\Mng\Entities\MngJumpPlusRewardBundle $mngJumpPlusRewardBundle */
            $mngSchedule = $mngJumpPlusRewardBundle->getMngJumpPlusRewardSchedule();

            $dynJumpPlusReward = $dynJumpPlusRewards->get($mngScheduleId);
            if ($dynJumpPlusReward === null) {
                continue;
            }

            $mngRewards = $mngJumpPlusRewardBundle->getMngJumpPlusRewards();
            if ($mngRewards->isEmpty()) {
                // 報酬設定がない場合は、受け取り対象外とみなしてスキップする。
                // 報酬受け取りをするための機能なので、報酬がないのは異常な状況と判断。
                // 他サービスからのユーザー流入を目的とした機能なので、GLOWに来てくれたのに報酬がない状態は避けるべき。
                // あとで報酬設定を追加すれば報酬配布できる状態にしておくために、受け取り対象外とみなしてスキップさせる
                continue;
            }

            $jumpPlusRewards = collect();
            foreach ($mngRewards as $mngReward) {
                /** @var \App\Domain\Resource\Mng\Entities\MngJumpPlusRewardEntity $mngReward */
                $jumpPlusRewards->push(
                    new JumpPlusReward(
                        $mngReward->getResourceType(),
                        $mngReward->getResourceId(),
                        $mngReward->getResourceAmount(),
                        $mngScheduleId,
                        $mngSchedule->getEndAt(),
                    )
                );
            }

            $jumpPlusRewardBundles->push(
                new JumpPlusRewardBundle(
                    $dynJumpPlusReward,
                    $jumpPlusRewards,
                )
            );
        }

        return $jumpPlusRewardBundles;
    }

    /**
     * 報酬受取済ステータスへ更新する
     *
     * usr_jump_plus_rewardsテーブルにレコードを作成することで受け取り済み管理を行う。
     * DynamoDBの更新は遅延実行として登録する。
     *
     * @param string $usrUserId
     * @param Collection<DynJumpPlusRewardEntity> $dynJumpPlusRewards
     * @return void
     */
    public function markRewardsAsReceived(string $usrUserId, Collection $dynJumpPlusRewards): void
    {
        if ($dynJumpPlusRewards->isEmpty()) {
            return;
        }

        $this->usrJumpPlusRewardRepository->createByMngJumpPlusRewardScheduleIds(
            $usrUserId,
            $dynJumpPlusRewards->map->getMngJumpPlusRewardScheduleId(),
        );

        // DynamoDBのreceiveRewards実行をDBトランザクション終了後の遅延実行として登録
        $this->deferredTaskService->registerAfterTransaction(function () use ($dynJumpPlusRewards) {
            $this->dynJumpPlusRewardRepository->receiveRewards($dynJumpPlusRewards);
        });
    }
}
