using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Core.Data.Translators
{
    public class TradeShopItemResultDataTranslator
    {
        public static TradeShopItemResultModel ToTradeShopItemResultModel(
            TradeShopItemResultData tradeShopItemResultData)
        {
            var shopData = tradeShopItemResultData.UsrShopItems;
            var userShopModels = shopData
                .Select(data => new UserShopItemModel(
                    new MasterDataId(data.MstShopItemId),
                    new ShopItemTradeCount(data.TradeCount),
                    new ShopItemTradeCount(data.TradeTotalCount)))
                .ToList();

            var itemData = tradeShopItemResultData.UsrItems;
            var userItemModels = itemData
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var parameterData = tradeShopItemResultData.UsrParameter;
            var userParameterModel = new UserParameterModel(
                new UserLevel(parameterData.Level),
                new UserExp(parameterData.Exp),
                new Coin(parameterData.Coin),
                new Stamina(parameterData.Stamina),
                parameterData.StaminaUpdatedAt,
                new FreeDiamond(parameterData.FreeDiamond),
                new PaidDiamondIos(parameterData.PaidDiamondIos),
                new PaidDiamondAndroid(parameterData.PaidDiamondAndroid),
                new UserDailyBuyStamina(parameterData.DailyBuyStaminaDiamondLimit,parameterData.DailyBuyStaminaAdLimit));

            return new TradeShopItemResultModel(
                userShopModels,
                userParameterModel,
                userItemModels);

        }
    }
}
