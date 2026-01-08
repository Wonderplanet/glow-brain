<?php

declare(strict_types=1);

namespace App\Domain\Item\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Item\Repositories\LogItemRepository;
use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Resource\Entities\LogTriggers\LogTrigger;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrItemService
{
    public function __construct(
        // Repository
        private UsrItemRepository $usrItemRepository,
        private MstItemRepository $mstItemRepository,
        private LogItemRepository $logItemRepository,
        // Service
        private ItemMissionTriggerService $itemMissionTriggerService,
        private MstConfigService $mstConfigService,
    ) {
    }

    /**
     * アイテムを消費する
     *
     * @param string $usrId
     * @param string $mstItemId
     * @param int $consumeAmount
     * @param LogTrigger $logTrigger
     * @return UsrItemInterface|null 対象アイテムのusr_itemsレコードがないかつ$consumeAmountが0のときはnull
     * @throws GameException
     */
    public function consumeItem(
        string $usrId,
        string $mstItemId,
        int $consumeAmount,
        LogTrigger $logTrigger,
    ): ?UsrItemInterface {
        return $this->consumeItems(
            $usrId,
            collect([$mstItemId => $consumeAmount]),
            $logTrigger,
        )->first();
    }

    /**
     * 複数のアイテムを消費する
     * 1つでも消費できないアイテムがあった場合は、例外を投げる
     *
     * @param Collection $consumeAmountByMstItemId key: mst_item_id, value: consumeAmount
     * @param LogTrigger $logTrigger 消費経緯データ
     * @return Collection<UsrItemInterface> 消費したアイテムのコレクション
     * @throws GameException
     */
    public function consumeItems(
        string $usrUserId,
        Collection $consumeAmountByMstItemId,
        LogTrigger $logTrigger,
    ): Collection {
        if ($consumeAmountByMstItemId->isEmpty()) {
            return collect();
        }

        $usrItems = $this->usrItemRepository->getListByMstItemIds($usrUserId, $consumeAmountByMstItemId->keys());
        $logItems = collect();
        foreach ($consumeAmountByMstItemId as $mstItemId => $consumeAmount) {
            if ($consumeAmount <= 0) {
                // 消費なしなのでスキップ
                continue;
            }
            $mstItemId = (string) $mstItemId;
            $usrItem = $usrItems->get($mstItemId);
            if (is_null($usrItem)) {
                throw new GameException(ErrorCode::ITEM_NOT_OWNED);
            }
            $beforeAmount = $usrItem->getAmount();
            $usrItem->subtractItemAmount($consumeAmount);

            $logItems->push(
                $this->logItemRepository->make(
                    $usrUserId,
                    LogResourceActionType::USE,
                    $mstItemId,
                    $beforeAmount,
                    $usrItem->getAmount(),
                    $logTrigger->getLogTriggerData(),
                ),
            );
        }
        $this->usrItemRepository->syncModels($usrItems);

        // ログ保存
        $this->logItemRepository->addModels($logItems);

        return $usrItems;
    }

    /**
     * 報酬インスタンスから、複数アイテム配布処理を実行
     *
     * @param Collection<BaseReward> $rewards
     */
    public function addItemByRewards(
        string $usrUserId,
        Collection $rewards,
        CarbonImmutable $now,
    ): void {
        if ($rewards->isEmpty()) {
            return;
        }

        $mstItemIds = $rewards->map(function (BaseReward $reward) {
            return $reward->getResourceId();
        })->unique();
        $this->mstItemRepository->getActiveItemsById($mstItemIds, $now, true);

        $usrItems = $this->usrItemRepository->getListByMstItemIds(
            $usrUserId,
            $mstItemIds,
        );

        $maxAmount = $this->mstConfigService->getUserItemMaxAmount();

        foreach ($rewards as $reward) {
            /** @var BaseReward $reward */
            $mstItemId = $reward->getResourceId();
            $addAmount = $reward->getAmount();

            $usrItem = $usrItems->get($mstItemId);
            if (is_null($usrItem)) {
                $usrItem = $this->usrItemRepository->create($usrUserId, $mstItemId, 0);
                $usrItems->put($mstItemId, $usrItem);
            }
            /** @var UsrItemInterface $usrItem */

            $beforeAmount = $usrItem->getAmount();
            $reward->setBeforeAmount($beforeAmount);

            $afterAmount = $beforeAmount + $addAmount;

            if ($afterAmount > $maxAmount) {
                $reward->setUnreceivedRewardReason(UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED);
                $afterAmount = $maxAmount;
            }

            $reward->setAfterAmount($afterAmount);
            $usrItem->setItemAmount($afterAmount);

            $reward->markAsSent();

            // ミッショントリガー送信
            $this->itemMissionTriggerService->sendItemCollectTrigger($mstItemId, $addAmount);
        }

        $this->usrItemRepository->syncModels($usrItems);
    }
}
