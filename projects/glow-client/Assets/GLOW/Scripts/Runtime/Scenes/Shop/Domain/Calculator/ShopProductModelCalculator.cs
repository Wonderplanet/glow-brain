using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.IdleIncentiveTop.Domain.Calculator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.Calculator
{
    public class ShopProductModelCalculator : IShopProductModelCalculator
    {
        [Inject] IIdleIncentiveRewardEvaluator IdleIncentiveRewardEvaluator { get; }
        [Inject] IIdleIncentiveRewardAmountCalculator IdleIncentiveRewardAmountCalculator { get; }

        public PurchasableCount CalculatePurchasableCountCurrent(MstShopItemModel mstShopItemModel, UserShopItemModel userShopItemModel)
        {
            if(userShopItemModel.IsEmpty())
                return mstShopItemModel.PurchasableCount;

            var purchasableCount = mstShopItemModel.PurchasableCount;
            var tradeCount = userShopItemModel.TradeCount;
            return purchasableCount - tradeCount;
        }

        public PurchasableCount CalculatePurchasableCountCurrent(MstStoreProductModel mstStoreProductModel, UserStoreProductModel userStoreProductModel)
        {
            if(userStoreProductModel.IsEmpty())
                return mstStoreProductModel.PurchasableCount;

            var purchasableCount = mstStoreProductModel.PurchasableCount;
            var purchaseCount = userStoreProductModel.PurchaseCount;
            return purchasableCount - purchaseCount;
        }

        public PlayerResourceAmount CalculateProductResourceAmount(MstShopItemModel mstShopItemModel, IdleIncentiveRewardAmount idleIncentiveBaseCoinAmount, TimeSpan intervalTime)
        {
            if (mstShopItemModel.ResourceType != ResourceType.IdleCoin)
                return new PlayerResourceAmount(mstShopItemModel.ProductResourceAmount.Value);
            else
            {
                // 経過時間 = 設定時間 * 60
                var totalCoin = IdleIncentiveRewardAmountCalculator.CalculatePlayerResourceAmount(
                    idleIncentiveBaseCoinAmount,
                    TimeSpan.FromMinutes(60 * mstShopItemModel.ProductResourceAmount.Value),
                    intervalTime,
                    PassEffectValue.Empty);
                return totalCoin.Value == 0 ? PlayerResourceAmount.Empty : totalCoin;
            }
        }

        public (IdleIncentiveRewardAmount baseCoinAmount, TimeSpan intervalMinutes)  GetBaseCoinAmountAndIntervalTime(IMstIdleIncentiveRepository idleIncentiveRepository, IGameRepository gameRepository)
        {
            var idleIncentive = idleIncentiveRepository.GetMstIdleIncentive();
            var mstIdleIncentiveRewardModel = IdleIncentiveRewardEvaluator.EvaluateHighestClearedStageReward();

            return (mstIdleIncentiveRewardModel.BaseCoinAmount, idleIncentive.RewardIncreaseIntervalMinutes);
        }
    }
}
