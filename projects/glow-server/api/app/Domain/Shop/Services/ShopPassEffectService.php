<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Resource\Entities\ShopPassActiveEffect;
use App\Domain\Resource\Mst\Repositories\MstShopPassEffectRepository;
use App\Domain\Shop\Enums\PassEffectType;
use App\Domain\Shop\Repositories\UsrShopPassRepository;
use Carbon\CarbonImmutable;

class ShopPassEffectService
{
    public function __construct(
        // Mst Repository
        private MstShopPassEffectRepository $mstShopPassEffectRepository,
        // Usr Repository
        private UsrShopPassRepository $usrShopPassRepository,
    ) {
    }

    public function getShopPassActiveEffectData(string $usrUserId, CarbonImmutable $now): ShopPassActiveEffect
    {
        $usrShopPasses = $this->usrShopPassRepository->getActiveList($usrUserId, $now);
        $idleRewardMultiplier = 0;
        $quickReceiveAddByDiamond = 0;
        $quickReceiveAddByAd = 0;
        $staminaAddRecoveryLimit = 0;
        if ($usrShopPasses->isEmpty()) {
            $idleRewardMultiplier = 1;
            return new ShopPassActiveEffect(
                $idleRewardMultiplier,
                $quickReceiveAddByDiamond,
                $quickReceiveAddByAd,
                $staminaAddRecoveryLimit,
            );
        }
        $mstShopPassIds = $usrShopPasses->map(fn($usrShopPass) => $usrShopPass->getMstShopPassId())->toArray();
        $mstShopPassEffects = $this->mstShopPassEffectRepository->getListByMstShopPassIds($mstShopPassIds);
        /** @var \App\Domain\Resource\Mst\Entities\MstShopPassEffectEntity $mstShopPassEffect */
        foreach ($mstShopPassEffects as $mstShopPassEffect) {
            switch ($mstShopPassEffect->getEffectType()) {
                case PassEffectType::IDLE_INCENTIVE_ADD_REWARD->value:
                    $idleRewardMultiplier += $mstShopPassEffect->getEffectValue();
                    break;
                case PassEffectType::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_DIAMOND->value:
                    $quickReceiveAddByDiamond += $mstShopPassEffect->getEffectValue();
                    break;
                case PassEffectType::IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_AD->value:
                    $quickReceiveAddByAd += $mstShopPassEffect->getEffectValue();
                    break;
                case PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value:
                    $staminaAddRecoveryLimit += $mstShopPassEffect->getEffectValue();
                    break;
            }
        }
        $idleRewardMultiplier = $idleRewardMultiplier === 0 ? 1 : $idleRewardMultiplier;
        return new ShopPassActiveEffect(
            $idleRewardMultiplier,
            $quickReceiveAddByDiamond,
            $quickReceiveAddByAd,
            $staminaAddRecoveryLimit,
        );
    }
}
