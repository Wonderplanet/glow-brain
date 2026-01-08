<?php

declare(strict_types=1);

namespace App\Domain\Shop\Delegators;

use App\Domain\Resource\Entities\ShopPassActiveEffect;
use App\Domain\Shop\Services\ShopPassEffectService;
use Carbon\CarbonImmutable;

class ShopPassEffectDelegator
{
    public function __construct(
        private readonly ShopPassEffectService $shopPassEffectService,
    ) {
    }

    public function getShopPassActiveEffectDataByUsrUserId(
        string $usrUserId,
        CarbonImmutable $now
    ): ShopPassActiveEffect {
        return $this->shopPassEffectService->getShopPassActiveEffectData($usrUserId, $now);
    }
}
