using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Core.Data.Translators
{
    public class MstExchangeDataTranslator
    {
        public static MstExchangeModel Translate(MstExchangeData exchange, MstExchangeI18nData i18n)
        {
            return new MstExchangeModel(
                new MasterDataId(exchange.Id),
                new MasterDataId(exchange.MstEventId),
                new MasterDataId(exchange.LineupGroupId),
                exchange.ExchangeTradeType,
                new ExchangeShopName(i18n.Name),
                new ExchangeContentBannerAssetKey(i18n.AssetKey),
                new ExchangeShopStartTime(exchange.StartAt),
                exchange.EndAt != null
                ? new ExchangeShopEndTime(exchange.EndAt.Value)
                : ExchangeShopEndTime.Unlimited,
                new SortOrder(exchange.DisplayOrder));
        }
    }
}
