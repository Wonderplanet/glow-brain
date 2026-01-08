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
        $addStamina = $this->calcAddStamina($usrUserId, $now, $recoveryStaminaPercentage);

        $usrUserBuyCount = $this->usrUserBuyCountRepository->findOrCreate($usrUserId);

        $this->resetDailyBuyStaminaAdCount($usrUserBuyCount);

        $this->validateAvailabilityForBuyStaminaAd($usrUserBuyCount);

        $usrUserBuyCount->setDailyBuyStaminaAd(
            $usrUserBuyCount->getDailyBuyStaminaAdCount() + 1,
            $now->toDateTimeString()
        );
        $this->usrUserBuyCountRepository->syncModel($usrUserBuyCount);

        $this->userService->addStamina($usrUserId, $addStamina, $now);

        // ミッショントリガー送信
        $this->adPlayService->adPlay(
            $usrUserId,
            ContentType::BUY_STAMINA->value,
            '',
            $now
        );
    }

    private function calcAddStamina(string $usrUserId, CarbonImmutable $now, int $recoveryStaminaPercentage): int
    {
        $usrUserParameter = $this->userService->recoveryStamina($usrUserId, $now);
        $currentStamina = $usrUserParameter->getStamina();
        $levelStamina = $this
            ->mstUserLevelRepository
            ->getByLevel($usrUserParameter->getLevel(), true)
            ->getStamina();
        $shopPassEffectData = $this->shopPassEffectDelegator->getShopPassActiveEffectDataByUsrUserId($usrUserId, $now);
        $totalStamina = $levelStamina + $shopPassEffectData->getStaminaAddRecoveryLimit();
        if ($totalStamina <= $currentStamina) {
            // スタミナ購入前の時点で最大値を超えている場合はエラー(最大値とイコールの場合は購入不可)
            throw new GameException(
                ErrorCode::USER_STAMINA_FULL,
                "stamina is full. (stamina: $currentStamina)"
            );
        }

        // 最大スタミナに対して指定％分スタミナを回復する
        $addStamina = (int) floor($totalStamina * $recoveryStaminaPercentage / 100);
        if ($this->mstConfigService->getUserStaminaMaxAmount() < $currentStamina + $addStamina) {
            // 購入によってシステム上限を超える場合はエラー
            throw new GameException(
                ErrorCode::USER_STAMINA_EXCEEDS_LIMIT,
                "exceeding the limit by making a buy. (stamina: $currentStamina addStamina: $addStamina)"
            );
        }
        return $addStamina;
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
        $addStamina = $this->calcAddStamina($usrUserId, $now, $recoveryStaminaPercentage);

        $costDiamond = $this->mstConfigService->getBuyStaminaDiamondAmount();
        $this->appCurrencyDelegator->consumeDiamond(
            $usrUserId,
            $costDiamond,
            $platform,
            $billingPlatform,
            new BuyStaminaTrigger($costDiamond)
        );
        $this->userService->addStamina($usrUserId, $addStamina, $now);
    }
}
