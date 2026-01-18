using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Extensions;

namespace GLOW.Scenes.PackShop.Domain.Calculator
{
    public class PackShopProductCalculator
    {
        public static PurchasableCount GetProductPurchasableCount(
            MstStoreProductModel productModel,
            IGameRepository gameRepository)
        {
            var usrStoreProduct = gameRepository.GetGameFetchOther().UserStoreProductModels
                .FirstOrDefault(usrProduct => usrProduct.ProductSubId == productModel.OprProductId);

            if (null == usrStoreProduct) return productModel.PurchasableCount;
            return productModel.PurchasableCount - usrStoreProduct.PurchaseCount;
        }

        public static PurchasableCount GetPackPurchasableCount(MstPackModel packModel, IGameRepository gameRepository)
        {
            var userTradePack = gameRepository.GetGameFetchOther().UserTradePackModels
                .FirstOrDefault(userPack => userPack.MstPackId.Value == packModel.Id.Value);

            if (userTradePack == null) return packModel.TradableCount;
            return packModel.TradableCount - userTradePack.DailyTradeCount;
        }

        public static bool IsValidPurchasableCount(MstStoreProductModel productModel, IGameRepository gameRepository)
        {
            var purchasableCount = GetProductPurchasableCount(productModel, gameRepository);
            return purchasableCount.IsPurchasable();
        }

        public static bool IsValidTradableCount(MstPackModel packModel, IGameRepository gameRepository)
        {
            var remainingCount = GetPackPurchasableCount(packModel, gameRepository);
            return remainingCount.IsPurchasable();
        }

        public static bool IsValidSaleHour(
            MstPackModel packModel,
            IGameRepository gameRepository,
            ITimeProvider timeProvider)
        {
            if (packModel.SaleHours.IsEmpty()) return true;
            var userConditionPackModel = gameRepository.GetGameFetchOther().UserConditionPackModels
                .FirstOrDefault(condition => condition.MstPackId == packModel.Id);
            if (null == userConditionPackModel) return false;

            var endHour = userConditionPackModel.StartDate.AddHours(packModel.SaleHours.Value);
            return timeProvider.Now <= endHour;
        }

        public static bool IsValidStageClear(MstPackModel packModel, IGameRepository gameRepository)
        {
            if (packModel.SaleConditionValue.Condition != SaleCondition.StageClear) return false;
            return  gameRepository.GetGameFetchOther().UserConditionPackModels
                .Any(condition => condition.MstPackId == packModel.Id);
        }

        public static  bool IsValidUserLevel(MstPackModel packModel, IGameRepository gameRepository)
        {
            var saleConditionValue = packModel.SaleConditionValue;
            if (saleConditionValue.Condition != SaleCondition.UserLevel) return false;
            return  gameRepository.GetGameFetchOther().UserConditionPackModels
                .Any(condition => condition.MstPackId == packModel.Id);
        }

        public static bool IsValidElapseDays(MstPackModel packModel)
        {
            if (packModel.SaleConditionValue.Condition != SaleCondition.ElaspeDays) return false;
            return true;
        }

        public static bool IsFirstTimePurchase(MstPackModel packModel, IGameRepository gameRepository)
        {
            if (!packModel.IsFirstTimeFree) return false;

            var userTradePack = gameRepository.GetGameFetchOther().UserTradePackModels
                .FirstOrDefault(userPack => userPack.MstPackId.Value == packModel.Id.Value);

            // UserTradePackModelが存在しない場合は初回購入
            if (userTradePack == null) return true;

            // 購入回数が0の場合は初回購入
            return userTradePack.DailyTradeCount == PurchaseCount.Zero;
        }

        public static bool ShouldApplyFirstTimeFree(MstPackModel packModel, IGameRepository gameRepository)
        {
            return packModel.IsFirstTimeFree && IsFirstTimePurchase(packModel, gameRepository);
        }
    }
}
