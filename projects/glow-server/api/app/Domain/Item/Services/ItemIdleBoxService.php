<?php

declare(strict_types=1);

namespace App\Domain\Item\Services;

use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\Item\Entities\ItemIdleBoxRewardExchange;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ItemIdleBoxService
{
    public function __construct(
        private MstItemRepository $mstItemRepository,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
    ) {
    }

    /**
     * 放置ボックスのリソースを実際に配布する実体へ変換する
     *
     * @param string $usrUserId
     * @param Collection<string, BaseReward> $rewards key: BaseReward.id
     * @param CarbonImmutable $now
     * @return Collection<string, BaseReward> key: BaseReward.id
     */
    public function convertIdleBoxToRealResources(
        string $usrUserId,
        Collection $rewards,
        CarbonImmutable $now
    ): Collection {
        if ($rewards->isEmpty()) {
            return $rewards;
        }

        // 放置ボックスアイテムのマスタを取得して、変換対象のrewardを特定する
        $rewardIdsByMstItemId = [];
        foreach ($rewards as $reward) {
            if ($reward->getType() === RewardType::ITEM->value) {
                $rewardIdsByMstItemId[$reward->getResourceId()][] = $reward->getId();
            }
        }
        $idleBoxMstItems = $this->mstItemRepository->getActiveItemsById(
            collect(array_keys($rewardIdsByMstItemId)),
            $now
        )->filter(fn(MstItemEntity $mstItem) => $mstItem->isIdleBox());

        $idleBoxRewards = collect();
        foreach ($rewardIdsByMstItemId as $mstItemId => $rewardIds) {
            if ($idleBoxMstItems->has($mstItemId)) {
                $idleBoxRewards = $idleBoxRewards->merge($rewards->only($rewardIds));
            }
        }
        if ($idleBoxRewards->isEmpty()) {
            return $rewards;
        }

        // 放置時間を考慮した報酬量を算出
        $itemIdleBoxRewardExchangeList = collect();
        foreach ($idleBoxRewards as $idleBoxReward) {
            /** @var ?MstItemEntity $mstIdleBoxItem */
            $mstIdleBoxItem = $idleBoxMstItems->get($idleBoxReward->getResourceId());
            if (is_null($mstIdleBoxItem)) {
                continue;
            }
            $idleMinutes = (int) ((int) $mstIdleBoxItem->getIdleBoxMinutes() * 60 * $idleBoxReward->getAmount());
            $itemIdleBoxRewardExchangeList->push(
                new ItemIdleBoxRewardExchange($idleBoxReward, $mstIdleBoxItem->getItemType(), $idleMinutes)
            );
        }
        $itemIdleBoxRewardExchangeList = $this->idleIncentiveDelegator->calcIdleBoxRewardAmounts(
            $usrUserId,
            $itemIdleBoxRewardExchangeList,
            $now,
        );
        $itemIdleBoxRewardExchangeList = $itemIdleBoxRewardExchangeList->keyBy(
            fn(ItemIdleBoxRewardExchange $itemIdleBoxRewardExchange) => $itemIdleBoxRewardExchange->getRewardId()
        );

        // TODO: ない場合はmst_not_found出したいが、報酬にidle_rank_up_materialがないなら取得不要なのでその条件を加えたい
        $mstRankUpMaterialItem = $this->mstItemRepository
            ->getActiveItemsByItemType(ItemType::RANK_UP_MATERIAL->value, $now)
            ->first();

        foreach ($itemIdleBoxRewardExchangeList as $itemIdleBoxRewardExchange) {
            /** @var ItemIdleBoxRewardExchange $itemIdleBoxRewardExchange */
            $amount = $itemIdleBoxRewardExchange->getAfterAmount();

            /** @var ?MstItemEntity $mstIdleBoxItem */
            $mstIdleBoxItem = $idleBoxMstItems->get($itemIdleBoxRewardExchange->getResourceId());
            if (is_null($mstIdleBoxItem)) {
                continue;
            }

            if ($mstIdleBoxItem->isIdleCoinBox()) {
                $rewardData = new RewardDto(RewardType::COIN->value, null, $amount);
            } elseif ($mstIdleBoxItem->isIdleRankUpMaterialBox()) {
                $rewardData = new RewardDto(RewardType::ITEM->value, $mstRankUpMaterialItem->getId(), $amount);
            } else {
                continue;
            }

            $reward = $rewards->get($itemIdleBoxRewardExchange->getRewardId());
            $reward->setRewardData($rewardData);
            $reward->setRewardConvertedReason(RewardConvertedReason::CONVERT_IDLE_BOX);
        }

        return $rewards;
    }
}
