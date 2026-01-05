using System;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.Shop.Domain.Calculator
{
    public interface IShopProductModelCalculator
    {
        PurchasableCount CalculatePurchasableCountCurrent(
            MstShopItemModel mstShopItemModel,
            UserShopItemModel userShopItemModel);

        PurchasableCount CalculatePurchasableCountCurrent(
            MstStoreProductModel mstStoreProductModel,
            UserStoreProductModel userStoreProductModel);

        PlayerResourceAmount CalculateProductResourceAmount(
            MstShopItemModel mstShopItemModel,
            IdleIncentiveRewardAmount idleIncentiveBaseCoinAmount,
            TimeSpan intervalTime);

        (IdleIncentiveRewardAmount baseCoinAmount, TimeSpan intervalMinutes) GetBaseCoinAmountAndIntervalTime(
            IMstIdleIncentiveRepository idleIncentiveRepository,
            IGameRepository gameRepository);
    }
}
