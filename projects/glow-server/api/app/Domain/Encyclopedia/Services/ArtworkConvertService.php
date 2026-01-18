<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Services;

use App\Domain\Encyclopedia\Constants\EncyclopediaConstant;
use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Repositories\MstArtworkRepository;
use Illuminate\Support\Collection;

class ArtworkConvertService
{
    public function __construct(
        private MstArtworkRepository $mstArtworkRepository,
        private UsrArtworkRepository $usrArtworkRepository,
    ) {
    }

    /**
     * 重複した原画が配布される場合にコインに変換する
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     */
    public function convertDuplicatedArtworkToCoin(
        string $usrUserId,
        Collection $rewards,
    ): void {
        if ($rewards->isEmpty()) {
            return;
        }

        $targetRewards = collect();
        $uniqueMstArtworkIds = collect();
        foreach ($rewards as $reward) {
            if ($reward->getType() !== RewardType::ARTWORK->value) {
                continue;
            }
            $mstArtworkId = $reward->getResourceId();
            $uniqueMstArtworkIds->put($mstArtworkId, $mstArtworkId);
            $targetRewards->push($reward);
        }

        if ($uniqueMstArtworkIds->isEmpty()) {
            return;
        }

        $validMstArtworks = $this->mstArtworkRepository->getByIds($uniqueMstArtworkIds->keys());
        if ($validMstArtworks->isEmpty()) {
            return;
        }

        /** @var Collection<string, \App\Domain\Resource\Mst\Entities\MstArtworkEntity> $validMstArtworkMap */
        $validMstArtworkMap = $validMstArtworks->keyBy(
            /** @param \App\Domain\Resource\Mst\Entities\MstArtworkEntity $mstArtwork */
            fn($mstArtwork): string => $mstArtwork->getId()
        );

        $convertAmount = EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;

        $ownedMstArtworkIds = $this->usrArtworkRepository
            ->getByMstArtworkIds($usrUserId, $validMstArtworkMap->keys())
            ->mapWithKeys(function ($usrArtwork) {
                return [$usrArtwork->getMstArtworkId() => $usrArtwork->getMstArtworkId()];
            });

        foreach ($targetRewards as $reward) {
            /** @var \App\Domain\Resource\Entities\Rewards\BaseReward $reward */
            $mstArtworkId = $reward->getResourceId();
            if ($mstArtworkId === null) {
                // resource_idがnullの場合は配布対象から除外
                $rewards->forget($reward->getId());
                continue;
            }

            // 初獲得原画の場合
            if ($ownedMstArtworkIds->has($mstArtworkId) === false) {
                $ownedMstArtworkIds->put($mstArtworkId, $mstArtworkId);

                if ($reward->getAmount() <= 1) {
                    continue;
                }

                // 初獲得分と重複分のRewardインスタンスを分離する
                $noConvertReward = $reward->divideRewardByAmount(1);
                $rewards->put($noConvertReward->getId(), $noConvertReward);
            }

            // 重複獲得原画の変換
            // Rewardの変換
            $mstArtwork = $validMstArtworkMap->get($mstArtworkId);
            if ($mstArtwork === null) {
                // 無効なマスタデータの場合は配布対象から除外する
                $rewards->forget($reward->getId());
                continue;
            }
            $reward->setRewardData(new RewardDto(
                RewardType::COIN->value,
                null,
                $reward->getAmount() * $convertAmount,
            ));
            $reward->setRewardConvertedReason(RewardConvertedReason::DUPLICATED_ARTWORK);
        }
        // NOTE: ミッショントリガーは ArtworkSendService 経由で grantArtworksWithFragments が呼ばれた際に送信される
    }
}
