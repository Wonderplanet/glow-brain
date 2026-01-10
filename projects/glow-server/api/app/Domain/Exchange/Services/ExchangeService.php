<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Exchange\Constants\ExchangeConstant;
use App\Domain\Exchange\Enums\ExchangeCostType;
use App\Domain\Exchange\Enums\ExchangeTradeType;
use App\Domain\Exchange\Models\UsrExchangeLineupInterface;
use App\Domain\Exchange\Repositories\LogExchangeActionRepository;
use App\Domain\Exchange\Repositories\UsrExchangeLineupRepository;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\LogTriggers\ExchangeTradeLogTrigger;
use App\Domain\Resource\Entities\Rewards\ExchangeTradeReward;
use App\Domain\Resource\Mst\Entities\MstExchangeCostEntity;
use App\Domain\Resource\Mst\Entities\MstExchangeEntity;
use App\Domain\Resource\Mst\Entities\MstExchangeLineupEntity;
use App\Domain\Resource\Mst\Entities\MstExchangeRewardEntity;
use App\Domain\Resource\Mst\Repositories\MstExchangeCostRepository;
use App\Domain\Resource\Mst\Repositories\MstExchangeLineupRepository;
use App\Domain\Resource\Mst\Repositories\MstExchangeRepository;
use App\Domain\Resource\Mst\Repositories\MstExchangeRewardRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ExchangeService
{
    public function __construct(
        private MstExchangeRepository $mstExchangeRepository,
        private MstExchangeLineupRepository $mstExchangeLineupRepository,
        private MstExchangeCostRepository $mstExchangeCostRepository,
        private MstExchangeRewardRepository $mstExchangeRewardRepository,
        private UsrExchangeLineupRepository $usrExchangeLineupRepository,
        private LogExchangeActionRepository $logExchangeActionRepository,
        private RewardDelegator $rewardDelegator,
        private UserDelegator $userDelegator,
        private ItemDelegator $itemDelegator,
        private Clock $clock,
    ) {
    }

    /**
     * リセットが必要かどうかを判定
     * NormalExchangeTradeのみ月次リセット対象
     */
    private function needsReset(UsrExchangeLineupInterface $usrExchangeLineup, MstExchangeEntity $mstExchange): bool
    {
        $exchangeTradeType = ExchangeTradeType::tryFrom($mstExchange->getExchangeTradeType());
        if ($exchangeTradeType === null || !$exchangeTradeType->isMonthlyResetTarget()) {
            return false;
        }

        return $this->clock->isFirstMonth($usrExchangeLineup->getResetAt());
    }

    /**
     * 交換回数をリセット（実際にDBに保存せずモデルのみ更新）
     */
    public function applyResetIfNeeded(
        UsrExchangeLineupInterface $usrExchangeLineup,
        CarbonImmutable $now,
        MstExchangeEntity $mstExchange
    ): void {
        if ($this->needsReset($usrExchangeLineup, $mstExchange)) {
            $usrExchangeLineup->resetTradeCount($now);
        }
    }

    /**
     * リセット適用済みの交換ラインナップ一覧を取得（DBには保存しない）
     * 開催中の交換所に対応するラインナップのみ取得
     *
     * @return Collection<UsrExchangeLineupInterface>
     */
    public function fetchResetUsrExchangeLineupsWithoutSyncModels(string $usrUserId, CarbonImmutable $now): Collection
    {
        $activeMstExchangeMap = $this->mstExchangeRepository->getActiveMap($now);
        $usrExchangeLineups = $this->usrExchangeLineupRepository->getListByMstExchangeIds(
            $usrUserId,
            $activeMstExchangeMap->keys()
        );

        foreach ($usrExchangeLineups as $usrExchangeLineup) {
            $mstExchange = $activeMstExchangeMap->get($usrExchangeLineup->getMstExchangeId());
            if ($mstExchange !== null) {
                $this->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange);
            }
        }

        return $usrExchangeLineups;
    }

    /**
     * 交換可能かバリデーション
     */
    public function validateExchange(
        UsrExchangeLineupInterface $usrExchangeLineup,
        MstExchangeEntity $mstExchange,
        MstExchangeLineupEntity $mstLineup,
        int $tradeCount,
        CarbonImmutable $now
    ): void {
        // 1回あたりの交換数上限チェック
        if ($tradeCount > ExchangeConstant::MAX_TRADE_COUNT_PER_REQUEST) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "exceeded exchange trade count max per request. (tradeCount: $tradeCount)"
            );
        }

        // 交換所の期間チェック

        $startAt = CarbonImmutable::parse($mstExchange->getStartAt());

        // 開始日時より前の場合はエラー
        if ($now->lt($startAt)) {
            throw new GameException(
                ErrorCode::EXCHANGE_NOT_TRADE_PERIOD,
                "Exchange is not available yet. (mst_exchange_id: {$mstExchange->getId()})"
            );
        }

        // 終了日時が設定されている場合は終了日時チェック
        if ($mstExchange->getEndAt() !== null) {
            $endAt = CarbonImmutable::parse($mstExchange->getEndAt());
            if ($now->gt($endAt)) {
                throw new GameException(
                    ErrorCode::EXCHANGE_NOT_TRADE_PERIOD,
                    "Exchange has ended. (mst_exchange_id: {$mstExchange->getId()})"
                );
            }
        }

        // 交換所とラインナップの整合性チェック
        if ($mstLineup->getGroupId() !== $mstExchange->getLineupGroupId()) {
            throw new GameException(
                ErrorCode::EXCHANGE_LINEUP_MISMATCH,
                "Exchange lineup does not belong to the exchange. " .
                "(mst_exchange_id: {$mstExchange->getId()}, mst_exchange_lineup_id: {$mstLineup->getId()})"
            );
        }

        // 交換上限チェック
        $tradableCount = $mstLineup->getTradableCount();
        if (!$usrExchangeLineup->canTrade($tradableCount, $tradeCount)) {
            throw new GameException(
                ErrorCode::EXCHANGE_LINEUP_TRADE_LIMIT_EXCEEDED,
                "Exchange limit exceeded. ",
            );
        }
    }

    /**
     * コスト消費処理
     *
     * @param Collection<MstExchangeCostEntity> $mstCosts
     */
    public function consumeCosts(
        string $usrUserId,
        string $mstExchangeId,
        string $mstExchangeLineupId,
        Collection $mstCosts,
        int $tradeCount,
        CarbonImmutable $now
    ): void {
        $logTrigger = new ExchangeTradeLogTrigger($mstExchangeId, $mstExchangeLineupId);

        // コストをタイプごとに集計
        $totalCoinCost = 0;
        $mstItemCosts = [];

        foreach ($mstCosts as $mstCost) {
            /** @var MstExchangeCostEntity $mstCost */
            $totalAmount = $mstCost->getCostAmount() * $tradeCount;

            if ($mstCost->getCostType() === ExchangeCostType::COIN->value) {
                $totalCoinCost += $totalAmount;
            } elseif ($mstCost->getCostType() === ExchangeCostType::ITEM->value) {
                $mstItemId = $mstCost->getCostId();
                if (StringUtil::isNotSpecified($mstItemId)) {
                    throw new GameException(
                        ErrorCode::MST_NOT_FOUND,
                        "Cost ID is required for Item type cost"
                    );
                }
                if (!isset($mstItemCosts[$mstItemId])) {
                    $mstItemCosts[$mstItemId] = 0;
                }
                $mstItemCosts[$mstItemId] += $totalAmount;
            } else {
                throw new GameException(
                    ErrorCode::MST_NOT_FOUND,
                    "Unknown cost type: {$mstCost->getCostType()}"
                );
            }
        }

        // Coinコストを一括で消費
        if ($totalCoinCost > 0) {
            $this->userDelegator->consumeCoin(
                $usrUserId,
                $totalCoinCost,
                $now,
                $logTrigger
            );
        }

        // Itemコストを一括で消費
        if (count($mstItemCosts) > 0) {
            $this->itemDelegator->useItemByMstItemIds(
                $usrUserId,
                collect($mstItemCosts),
                $logTrigger
            );
        }
    }


    /**
     * 報酬をRewardDelegatorに追加
     *
     * @param Collection<MstExchangeRewardEntity> $mstRewards
     */
    public function addRewards(
        string $mstExchangeId,
        string $mstExchangeLineupId,
        Collection $mstRewards,
        int $tradeCount
    ): void {
        $exchangeTradeRewards = [];
        foreach ($mstRewards as $mstReward) {
            /** @var MstExchangeRewardEntity $mstReward */
            $totalAmount = $mstReward->getResourceAmount() * $tradeCount;

            $exchangeTradeRewards[] = new ExchangeTradeReward(
                $mstReward->getResourceType(),
                $mstReward->getResourceId(),
                $totalAmount,
                $mstExchangeId,
                $mstExchangeLineupId,
            );
        }

        $this->rewardDelegator->addRewards(collect($exchangeTradeRewards));
    }

    /**
     * 交換実行
     */
    public function trade(
        string $usrUserId,
        string $mstExchangeId,
        string $mstExchangeLineupId,
        int $tradeCount,
        CarbonImmutable $now,
        int $platform,
    ): void {
        // マスタデータ取得
        $mstExchange = $this->mstExchangeRepository->getById($mstExchangeId, true);
        $mstLineup = $this->mstExchangeLineupRepository->getById($mstExchangeLineupId, true);
        $mstCosts = $this->mstExchangeCostRepository->getByLineupId($mstExchangeLineupId);
        $mstRewards = $this->mstExchangeRewardRepository->getByLineupId($mstExchangeLineupId);

        $usrExchangeLineup = $this->usrExchangeLineupRepository->getOrCreate(
            $usrUserId,
            $mstExchangeLineupId,
            $mstExchangeId,
            $now
        );

        // リセット判定を適用
        $this->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange);

        // バリデーション
        $this->validateExchange($usrExchangeLineup, $mstExchange, $mstLineup, $tradeCount, $now);

        // コスト消費
        $this->consumeCosts(
            $usrUserId,
            $mstExchangeId,
            $mstExchangeLineupId,
            $mstCosts,
            $tradeCount,
            $now
        );

        // 報酬をRewardDelegatorに追加
        $this->addRewards($mstExchangeId, $mstExchangeLineupId, $mstRewards, $tradeCount);
        $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);

        // 実際に配布された報酬を取得
        $sentRewards = $this->rewardDelegator->getSentRewards(ExchangeTradeReward::class);

        // 交換回数更新
        $usrExchangeLineup->incrementTradeCount($tradeCount);
        $this->usrExchangeLineupRepository->syncModel($usrExchangeLineup);

        // ログ記録
        $this->logExchangeActionRepository->create(
            $usrUserId,
            $mstExchangeId,
            $mstExchangeLineupId,
            $mstCosts,
            $sentRewards,
            $tradeCount
        );
    }
}
