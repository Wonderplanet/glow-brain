using GLOW.Core.Domain.ValueObjects;
using System;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Core.Domain.Models
{
    public record MstExchangeModel(
        MasterDataId Id,
        MasterDataId MstEventId,
        MasterDataId MstGroupId,
        ExchangeTradeType TradeType,
        ExchangeShopName Name,
        ExchangeContentBannerAssetKey BannerAssetKey,
        ExchangeShopStartTime StartAt,
        ExchangeShopEndTime EndAt,
        SortOrder SortOrder)
    {
        public static MstExchangeModel Empty { get; } =
            new MstExchangeModel(
                MasterDataId.Empty,
                MasterDataId.Empty,
                MasterDataId.Empty,
                ExchangeTradeType.NormalExchangeTrade,
                ExchangeShopName.Empty,
                ExchangeContentBannerAssetKey.Empty,
                ExchangeShopStartTime.Empty,
                ExchangeShopEndTime.Empty,
                SortOrder.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
