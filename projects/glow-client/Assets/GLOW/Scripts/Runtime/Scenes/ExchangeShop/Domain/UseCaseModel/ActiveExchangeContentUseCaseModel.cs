using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCaseModel
{
    public record ActiveExchangeContentUseCaseModel(
        MasterDataId Id,
        MasterDataId MstGroupId,
        ExchangeContentBannerAssetKey BannerAssetKey,
        ExchangeTradeType TradeType,
        RemainingTimeSpan LimitTime,
        ExchangeShopEndTime EndAt,
        SortOrder SortOrder);
}
