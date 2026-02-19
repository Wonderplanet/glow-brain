<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Shop\Models\UsrShopPassInterface;
use App\Domain\Shop\Models\UsrStoreProductInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;

class ShopPurchaseResultData
{
    /**
     * @param UsrStoreProductInterface $usrStoreProduct
     * @param UsrShopPassInterface|null $usrShopPass
     * @param Collection<\App\Domain\Resource\Entities\Rewards\ShopPassReward> $shopPassRewards
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     * @param UsrParameterData $usrUserParameter
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface> $usrUnits
     * @param Collection<\App\Domain\Shop\Models\UsrTradePackInterface> $usrTradePacks
     * @param UsrStoreInfoEntity|null $usrStoreInfo
     */
    public function __construct(
        public UsrStoreProductInterface $usrStoreProduct,
        public ?UsrShopPassInterface $usrShopPass,
        public Collection $shopPassRewards,
        public Collection $rewards,
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $usrTradePacks,
        public ?UsrStoreInfoEntity $usrStoreInfo,
    ) {
    }
}
