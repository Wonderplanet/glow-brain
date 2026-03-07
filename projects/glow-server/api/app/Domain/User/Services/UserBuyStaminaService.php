<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Services\AdPlayService;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\BuyStaminaTrigger;
use App\Domain\Resource\Mst\Repositories\MstUserLevelRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Shop\Delegators\ShopPassEffectDelegator;
use App\Domain\User\Models\UsrUserBuyCountInterface;
use App\Domain\User\Repositories\UsrUserBuyCountRepository;
use Carbon\CarbonImmutable;

class UserBuyStaminaService
{
    public function __construct(
        private AppCurrencyDelegator $appCurrencyDelegator,
        private Clock $clock,
        private MstConfigService $mstConfigService,
        private MstUserLevelRepository $mstUserLevelRepository,
        private UserService $userService,
        private UsrUserBuyCountRepository $usrUserBuyCountRepository,
        private AdPlayService $adPlayService,
        private ShopPassEffectDelegator $shopPassEffectDelegator,
    ) {
    }

    /**
     * 広告視聴でスタミナを購入する
     *
     * @throws GameException
     */
    public function buyStaminaAd(string $usrUserId, CarbonImmutable $now): void
    {
        $recoveryStaminaPercentage = $this->mstConfigService->getBuyStaminaAdPercentageOfMaxStamina();

        $usrUserBuyCount = $this->usrUserBuyCountRepository->findOrCreate($usrUserId);

        $this->resetDailyBuyStaminaAdCount($usrUserBuyCount);

        $this->validateAvailabilityForBuyStaminaAd($usrUserBuyCount);

        $usrUserBuyCount->setDailyBuyStaminaAd(
            $usrUserBuyCount->getDailyBuyStaminaAdCount() + 1,
            $now->toDateTimeString()
        );
        $this->usrUserBuyCountRepository->syncModel($usrUserBuyCount);

        // スタミナ回復実行（バリデーション・加算も含む）
        $this->recoverStaminaByPercent($usrUserId, $now, $recoveryStaminaPercentage, 1);

        // ミッショントリガー送信
        $this->adPlayService->adPlay(
            $usrUserId,
            ContentType::BUY_STAMINA->value,
            '',
            $now
        );
    }

    /**
     * ユーザのスタミナ購入回数を取得し、日付をリセットする(DB保存なし)
     * updateAndFetch用
     *
     * @param string $usrUserId
     * @return UsrUserBuyCountInterface
     */
    public function getUsrUserBuyCountAndReset(string $usrUserId): UsrUserBuyCountInterface
    {
        $usrUserBuyCount = $this->usrUserBuyCountRepository->findOrCreate($usrUserId);
        $this->resetDailyBuyStaminaAdCount($usrUserBuyCount, false);
        return $usrUserBuyCount;
    }

    private function resetDailyBuyStaminaAdCount(
        UsrUserBuyCountInterface $usrUserBuyCount,
        bool $withSyncModel = true
    ): void {
        $buyAt = $usrUserBuyCount->getDailyBuyStaminaAdAt();
        if (!is_null($buyAt) && !$this->clock->isFirstToday($buyAt)) {
            // 購入が今日初めてではないので何もしない
            return;
        }
        $usrUserBuyCount->setDailyBuyStaminaAdCount(0);
        if ($withSyncModel) {
            $this->usrUserBuyCountRepository->syncModel($usrUserBuyCount);
        }
    }

    private function validateAvailabilityForBuyStaminaAd(UsrUserBuyCountInterface $usrUserBuyCount): void
    {
        // 広告視聴インターバル検証
        $buyStaminaAdAt = $usrUserBuyCount->getDailyBuyStaminaAdAt();
        if (!is_null($buyStaminaAdAt)) {
            $intervalMinutes = $this->mstConfigService->getDailyBuyStaminaAdIntervalMinutes();
            $buyAvailableAt = CarbonImmutable::parse($buyStaminaAdAt)->addMinutes($intervalMinutes);
            if ($buyAvailableAt->isFuture()) {
                throw new GameException(
                    ErrorCode::USER_BUY_STAMINA_AD_DURING_INTERVAL,
                    "buy stamina ad interval not ended. (buy available at: {$buyAvailableAt->toDateTimeString()})"
                );
            }
        }

        // 購入回数検証
        $maxCount = $this->mstConfigService->getMaxDailyBuyStaminaAdCount();
        $buyCount = $usrUserBuyCount->getDailyBuyStaminaAdCount();
        if ($maxCount <= $buyCount) {
            throw new GameException(
                ErrorCode::USER_BUY_STAMINA_COUNT_LIMIT,
                "buy limit has been reached. (limit: $maxCount buyCount: $buyCount)"
            );
        }
    }

    /**
     * ダイヤモンドでスタミナを購入する
     *
     * @param string          $usrUserId
     * @param int             $platform
     * @param string          $billingPlatform
     * @param CarbonImmutable $now
     * @return void
     * @throws GameException
     */
    public function buyStaminaDiamond(
        string $usrUserId,
        int $platform,
        string $billingPlatform,
        CarbonImmutable $now
    ): void {
        $recoveryStaminaPercentage = $this->mstConfigService->getBuyStaminaDiamondPercentageOfMaxStamina();

        $costDiamond = $this->mstConfigService->getBuyStaminaDiamondAmount();
        $this->appCurrencyDelegator->consumeDiamond(
            $usrUserId,
            $costDiamond,
            $platform,
            $billingPlatform,
            new BuyStaminaTrigger($costDiamond)
        );

        // スタミナ回復実行（バリデーション・加算も含む）
        $this->recoverStaminaByPercent($usrUserId, $now, $recoveryStaminaPercentage, 1);
    }

    /**
     * スタミナ回復の共通処理
     *
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在時刻
     * @param int $baseAddStamina 基礎回復量
     * @param int $multiplier 基礎回復量に対する倍率（使用個数,回復回数）
     * @param int $beforeStamina 回復前のスタミナ量
     * @return int 実際の回復量
     */
    private function executeStaminaRecovery(
        string $usrUserId,
        CarbonImmutable $now,
        int $baseAddStamina,
        int $multiplier,
        int $beforeStamina,
    ): int {
        // システム上限を取得
        $systemMaxStamina = $this->mstConfigService->getUserStaminaMaxAmount();

        // スタミナがシステム上限の場合は全切り捨てのためエラー
        if ($beforeStamina >= $systemMaxStamina) {
            throw new GameException(
                ErrorCode::USER_STAMINA_FULL,
                "stamina is full. (stamina: $beforeStamina, systemMax: $systemMaxStamina)"
            );
        }

        // 使用可能個数の検証
        if ($baseAddStamina <= 0) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "baseAddStamina must be positive. (baseAddStamina: $baseAddStamina)",
            );
        }

        // 使用可能な最大個数を計算
        // 「部分回復許容のため、切り上げで計算（ceilを使用）」
        $maxUsableAmount = max(1, (int) ceil(($systemMaxStamina - $beforeStamina) / $baseAddStamina));

        if ($multiplier > $maxUsableAmount) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "amount exceeds usable limit. (amount: $multiplier, maxUsable: $maxUsableAmount)"
            );
        }

        // 実際の回復量を計算（使用個数 × 基礎回復量）
        $addStamina = $baseAddStamina * $multiplier;

        // 部分回復を許容してシステム上限までの実際の回復量を計算
        $actualAddStamina = min($beforeStamina + $addStamina, $systemMaxStamina) - $beforeStamina;

        $this->userService->addStamina($usrUserId, $actualAddStamina, $now);

        return $actualAddStamina;
    }

    /**
     * パーセント指定でスタミナを回復する
     *
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在時刻
     * @param int $percent ユーザー上限に対する回復割合（%）
     * @param int $multiplier 使用個数
     * @return int 実際の回復量
     */
    public function recoverStaminaByPercent(
        string $usrUserId,
        CarbonImmutable $now,
        int $percent,
        int $multiplier,
    ): int {
        // 自然回復を適用
        $usrUserParameter = $this->userService->recoveryStamina($usrUserId, $now);
        $beforeStamina = $usrUserParameter->getStamina();

        // ユーザーのスタミナ上限値を算出
        $levelStamina = $this
            ->mstUserLevelRepository
            ->getByLevel($usrUserParameter->getLevel(), true)
            ->getStamina();
        $shopPassEffectData = $this->shopPassEffectDelegator->getShopPassActiveEffectDataByUsrUserId($usrUserId, $now);
        $userLimitStamina = $levelStamina + $shopPassEffectData->getStaminaAddRecoveryLimit();

        // 基礎回復量を計算（パーセント指定: ユーザー上限 × パーセント / 100）
        $baseAddStamina = (int) floor($userLimitStamina * $percent / 100);

        return $this->executeStaminaRecovery($usrUserId, $now, $baseAddStamina, $multiplier, $beforeStamina);
    }

    /**
     * 固定値指定でスタミナを回復する
     *
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在時刻
     * @param int $fixedAmount 固定回復量
     * @param int $multiplier 使用個数
     * @return int 実際の回復量
     */
    public function recoverStaminaByFixed(
        string $usrUserId,
        CarbonImmutable $now,
        int $fixedAmount,
        int $multiplier,
    ): int {
        // 自然回復を適用
        $usrUserParameter = $this->userService->recoveryStamina($usrUserId, $now);
        $beforeStamina = $usrUserParameter->getStamina();

        // 基礎回復量（固定値指定: そのまま使用）
        $baseAddStamina = $fixedAmount;

        return $this->executeStaminaRecovery($usrUserId, $now, $baseAddStamina, $multiplier, $beforeStamina);
    }
}
