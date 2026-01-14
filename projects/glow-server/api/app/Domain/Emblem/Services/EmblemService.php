<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Constants\EmblemConstant;
use App\Domain\Emblem\Repositories\UsrEmblemRepository;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Repositories\MstEmblemRepository;
use Illuminate\Support\Collection;

class EmblemService
{
    public function __construct(
        private MstEmblemRepository $mstEmblemRepository,
        private UsrEmblemRepository $usrEmblemRepository,
        private EmblemMissionTriggerService $emblemMissionTriggerService,
    ) {
    }

    /**
     * エンブレムを所持しているかバリデーション
     * @param string $usrUserId
     * @param string $mstEmblemId
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function validateHasUsrEmblem(string $usrUserId, string $mstEmblemId): void
    {
        $this->mstEmblemRepository->getByIdWithError($mstEmblemId);
        $this->usrEmblemRepository->findByMstEmblemId($usrUserId, $mstEmblemId, true);
    }

    /**
     * 初期エンブレムを登録する
     * @param string $usrUserId
     */
    public function registerInitialEmblems(string $usrUserId): void
    {
        $mstEmblemIds = EmblemConstant::INITIAL_EMBLEM_MST_EMBLEM_IDS;
        $this->usrEmblemRepository->bulkCreate($usrUserId, $mstEmblemIds);
    }

    /**
     * 指定エンブレムを新規獲得する
     * エンブレムIDに重複があった場合は獲得されないので注意
     *
     * @param string $usrUserId
     * @param Collection<string> $mstEmblemIds mst_emblems.idの配列
     */
    public function addUsrEmblems(string $usrUserId, Collection $mstEmblemIds): void
    {
        $mstEmblemIds = $mstEmblemIds->unique();
        $validMstEmblems = $this->mstEmblemRepository->getByIds($mstEmblemIds);
        $validMstEmblemIds = $validMstEmblems->keys();

        $this->usrEmblemRepository->bulkCreate($usrUserId, $validMstEmblemIds->toArray());

        // ミッショントリガー送信
        $this->emblemMissionTriggerService->sendNewEmblemTrigger($validMstEmblems);
    }

    /**
     * 重複したエンブレムが配布される場合にコインに変換する
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     */
    public function convertDuplicatedEmblemToCoin(
        string $usrUserId,
        Collection $rewards,
    ): void {
        if ($rewards->isEmpty()) {
            return;
        }

        $targetRewards = collect();
        $uniqueMstEmblemIds = collect();
        foreach ($rewards as $reward) {
            if ($reward->getType() !== RewardType::EMBLEM->value) {
                continue;
            }
            $mstEmblemId = $reward->getResourceId();
            $uniqueMstEmblemIds->put($mstEmblemId, $mstEmblemId);
            $targetRewards->push($reward);
        }

        if ($uniqueMstEmblemIds->isEmpty()) {
            return;
        }

        $validMstEmblems = $this->mstEmblemRepository->getByIds($uniqueMstEmblemIds->keys());
        if ($validMstEmblems->isEmpty()) {
            return;
        }

        $convertAmount = EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN;

        $ownedMstEmblemIds = $this->usrEmblemRepository
            ->findByMstEmblemIds($usrUserId, $validMstEmblems->keys())
            ->mapWithKeys(function ($usrEmblem) {
                return [$usrEmblem->getMstEmblemId() => $usrEmblem->getMstEmblemId()];
            });

        $duplicatedMstEmblemIds = collect();
        foreach ($targetRewards as $reward) {
            /** @var \App\Domain\Resource\Entities\Rewards\BaseReward $reward */
            $mstEmblemId = $reward->getResourceId();

            // 初獲得エンブレムの場合
            if ($ownedMstEmblemIds->has($mstEmblemId) === false) {
                $ownedMstEmblemIds->put($mstEmblemId, $mstEmblemId);

                if ($reward->getAmount() <= 1) {
                    continue;
                }

                // 初獲得分と重複分のRewardインスタンスを分離する
                $noConvertReward = $reward->divideRewardByAmount(1);
                $rewards->put($noConvertReward->getId(), $noConvertReward);
            }

            // 重複獲得エンブレムの変換
            // ミッショントリガー用に変換前のエンブレムと数を記録
            $duplicatedMstEmblemIds = $duplicatedMstEmblemIds->pad(
                $duplicatedMstEmblemIds->count() + $reward->getAmount(),
                $mstEmblemId,
            );
            // Rewardの変換
            $mstEmblem = $validMstEmblems->get($mstEmblemId);
            if ($mstEmblem === null) {
                // 無効なマスタデータの場合は配布対象から除外する
                $rewards->forget($reward->getId());
                continue;
            }
            $reward->setRewardData(new RewardDto(
                RewardType::COIN->value,
                null,
                $reward->getAmount() * $convertAmount,
            ));
            $reward->setRewardConvertedReason(RewardConvertedReason::DUPLICATED_EMBLEM);
        }

        // ミッショントリガー
        $this->emblemMissionTriggerService->sendDuplicatedEmblemTrigger($duplicatedMstEmblemIds);
    }

    /**
     * エンブレムの図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstEmblemId
     * @throws GameException
     */
    public function markAsCollected(string $usrUserId, string $mstEmblemId): void
    {
        $usrEmblem =  $this->usrEmblemRepository->findByMstEmblemId($usrUserId, $mstEmblemId);

        // データがない
        if (is_null($usrEmblem)) {
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_DATA_NOT_FOUND,
                'emblem encyclopedia is new data not found. (' . $mstEmblemId . ')'
            );
        }
        // 取得したデータのis_new_encyclopediaが1かどうか
        if ($usrEmblem->isAlreadyCollected()) {
            // 取得したデータのis_new_encyclopediaが1でない
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_NOT_IS_NEW,
                'emblem encyclopedia not is new data . (' . $mstEmblemId . ')'
            );
        }
        $usrEmblem->markAsCollected();
        $this->usrEmblemRepository->syncModel($usrEmblem);
    }
}
