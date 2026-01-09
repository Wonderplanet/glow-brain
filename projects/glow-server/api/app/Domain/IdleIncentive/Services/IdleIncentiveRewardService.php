<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\IdleIncentive\Repositories\UsrIdleIncentiveRepository;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Entities\Rewards\IdleIncentiveReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstIdleIncentiveRewardEntity;
use App\Domain\Resource\Mst\Repositories\MstIdleIncentiveItemRepository;
use App\Domain\Resource\Mst\Repositories\MstIdleIncentiveRepository;
use App\Domain\Resource\Mst\Repositories\MstIdleIncentiveRewardRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class IdleIncentiveRewardService
{
    public function __construct(
        private MstIdleIncentiveRepository $mstIdleIncentiveRepository,
        private MstIdleIncentiveRewardRepository $mstIdleIncentiveRewardRepository,
        private MstIdleIncentiveItemRepository $mstIdleIncentiveItemRepository,
        private MstConfigService $mstConfigService,
        private UsrIdleIncentiveRepository $usrIdleIncentiveRepository,
    ) {
    }

    /**
     * 経過時間(分)を受け取り、放置収益の報酬を算出する。
     * ステージの進捗に応じて報酬は変わる。
     *
     * @param string  $usrUserId
     * @param integer $minutes
     * @param integer $rewardMultiplier
     * @return Collection<\App\Domain\Resource\Entities\Rewards\IdleIncentiveReward>
     * @throws GameException
     */
    public function calcRewards(
        string $usrUserId,
        CarbonImmutable $now,
        int $minutes,
        IdleIncentiveExecMethod $idleIncentiveExecMethod,
        int $rewardMultiplier = 1,
    ): Collection {
        $rewards = collect();

        $mstIdleIncentive = $this->mstIdleIncentiveRepository->getLast();
        $mstIdleInceReward = $this->getMstReward($usrUserId, $now);
        if (is_null($mstIdleInceReward)) {
            return collect();
        }

        $intervalMinutes = $mstIdleIncentive->getRewardIncreaseIntervalMinutes();

        // 経過時間分の報酬を計算して、ボーナスデータを作成

        // コイン報酬
        $coinAmount = $this->calcAmountByMinutes(
            bcmul($mstIdleInceReward->getBaseCoinAmount(), (string) $rewardMultiplier, 4),
            $intervalMinutes,
            $minutes,
        );
        $rewards->push(new IdleIncentiveReward(
            RewardType::COIN->value,
            null,
            $coinAmount,
            $idleIncentiveExecMethod,
        ));

        // 経験値報酬
        $expAmount = $this->calcAmountByMinutes(
            bcmul($mstIdleInceReward->getBaseExpAmount(), (string) $rewardMultiplier, 4),
            $intervalMinutes,
            $minutes,
        );
        $rewards->push(new IdleIncentiveReward(
            RewardType::EXP->value,
            null,
            $expAmount,
            $idleIncentiveExecMethod,
        ));

        // アイテム報酬
        $mstItemGroupId = $mstIdleInceReward->getMstIdleIncentiveItemGroupId();
        $mstIdleInceItems = $this->mstIdleIncentiveItemRepository->getByMstIdleIncentiveItemGroupId($mstItemGroupId);
        foreach ($mstIdleInceItems as $mstIdleInceItem) {
            /** @var \App\Domain\Resource\Mst\Entities\MstIdleIncentiveItemEntity $mstIdleInceItem */
            $itemAmount = $this->calcAmountByMinutes(
                // $mstIdleInceItem->getBaseAmount(),
                bcmul($mstIdleInceItem->getBaseAmount(), (string) $rewardMultiplier, 4),
                $intervalMinutes,
                $minutes,
            );

            $rewards->push(new IdleIncentiveReward(
                RewardType::ITEM->value,
                $mstIdleInceItem->getMstItemId(),
                $itemAmount,
                $idleIncentiveExecMethod,
            ));
        }

        return $rewards;
    }

    /**
     * ユーザーのステージ進捗に応じて報酬のマスタを取得
     */
    public function getMstReward(string $usrUserId, CarbonImmutable $now): ?MstIdleIncentiveRewardEntity
    {
        // usr_idle_incentives.reward_mst_stage_idから取得
        $usrIdleIncentive = $this->usrIdleIncentiveRepository->findOrCreate($usrUserId, $now);
        $mstStageId = $usrIdleIncentive->getRewardMstStageId();

        if (is_null($mstStageId)) {
            // reward_mst_stage_idがnullの場合は最低保証報酬として参照するステージIDをmst_configsから取得
            $mstStageId = $this->mstConfigService->getIdleIncentiveInitialRewardMstStageId();
            if (is_null($mstStageId)) {
                // mst_configsにも指定がない場合は、報酬なしとしてnullを返す
                return null;
            }
        }

        return $this->mstIdleIncentiveRewardRepository->getByMstStageId($mstStageId);
    }

    /**
     * 2025/05/21 探索連動アイテムの仕様は一旦廃止になったが、復活の可能性があるので、コードとしては残しておく。
     * 放置ボックスアイテムの報酬量を算出する
     *
     * @param string $usrUserId
     * @param Collection<\App\Domain\Item\Entities\ItemIdleBoxRewardExchangeInterface> $itemIdleBoxRewardExchangeList
     * @return Collection<\App\Domain\Item\Entities\ItemIdleBoxRewardExchangeInterface>
     */
    public function calcIdleBoxRewardAmounts(
        string $usrUserId,
        Collection $itemIdleBoxRewardExchangeList,
        CarbonImmutable $now,
    ): Collection {
        $mstIdleIncentive = $this->mstIdleIncentiveRepository->getLast();
        $mstIdleIncentiveReward = $this->getMstReward($usrUserId, $now);
        // マスタがない場合は空で返す
        if (is_null($mstIdleIncentiveReward)) {
            return collect();
        }

        $intervalMinutes = $mstIdleIncentive->getRewardIncreaseIntervalMinutes();
        $baseCoinAmount = $mstIdleIncentiveReward->getBaseCoinAmount();
        // 仕様変更でbase_rank_up_material_amount列がなくなったので、仮で0を入れている
        // $baseRankUpMaterialAmount = $mstIdleIncentiveReward->getBaseRankUpMaterialAmount();
        $baseRankUpMaterialAmount = 0;

        // itemTypeに応じて、計算に使う基本報酬量を変える
        /**
         * @var Collection<string, numeric-string> $baseAmountMap
         */
        $baseAmountMap = collect([
            ItemType::IDLE_COIN_BOX->value => $baseCoinAmount,
            ItemType::IDLE_RANK_UP_MATERIAL_BOX->value => $baseRankUpMaterialAmount,
        ]);

        $result = collect();
        foreach ($itemIdleBoxRewardExchangeList as $itemIdleBoxRewardExchange) {
            /** @var \App\Domain\Item\Entities\ItemIdleBoxRewardExchangeInterface $itemIdleBoxRewardExchange */
            /** @var numeric-string|null $baseAmount */
            $baseAmount = $baseAmountMap->get($itemIdleBoxRewardExchange->getItemType());
            // 該当なしの場合は、報酬量の計算をスキップ
            if (is_null($baseAmount)) {
                continue;
            }

            $amount = $this->calcAmountByMinutes(
                (string) $baseAmount,
                $intervalMinutes,
                $itemIdleBoxRewardExchange->getIdleMinutes(),
            );
            $itemIdleBoxRewardExchange->setAfterAmount($amount);

            $result->push($itemIdleBoxRewardExchange);
        }

        return $result;
    }

    /**
     * 放置収益で獲得できるコインの量を算出する
     *
     * @param string $usrUserId
     * @param Collection<int> $minutesList
     * @return Collection<int|string, int> key: 放置時間(分), value: 報酬量
     */
    public function calcCoinRewardAmounts(string $usrUserId, Collection $minutesList, CarbonImmutable $now): Collection
    {
        $mstIdleIncentive = $this->mstIdleIncentiveRepository->getLast();
        $mstIdleIncentiveReward = $this->getMstReward($usrUserId, $now);
        // マスタがない場合は空で返す
        if (is_null($mstIdleIncentiveReward)) {
            return collect();
        }

        $intervalMinutes = $mstIdleIncentive->getRewardIncreaseIntervalMinutes();
        $baseAmount = $mstIdleIncentiveReward->getBaseCoinAmount();

        return $minutesList->mapWithKeys(function ($minutes) use ($baseAmount, $intervalMinutes) {
            return [$minutes => $this->calcAmountByMinutes($baseAmount, $intervalMinutes, $minutes)];
        });
    }

    /**
     * 放置収益の報酬量を計算する
     *
     * @param numeric-string $baseAmount ベース獲得量
     * @param int $intervalMinutes インターバル(分)
     * @param int $minutes 経過時間(分)
     * @return int
     */
    public function calcAmountByMinutes(string $baseAmount, int $intervalMinutes, int $minutes): int
    {
        try {
            $rewardCount = (int) floor($minutes / $intervalMinutes);
            // 負の値なら0にする
            $rewardCount = max(0, $rewardCount);
            $amount = bcmul($baseAmount, (string)$rewardCount, 0); // 小数点以下切り捨て
            return (int) $amount;
        } catch (\Exception $e) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "Failed to calculate idle reward amount.
                (base: $baseAmount, interval: $intervalMinutes, minutes: $minutes)"
            );
        }
    }
}
