using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record ExchangeContentCellViewModel(
        MasterDataId MstExchangeId,
        MasterDataId MstGroupId,
        ExchangeTradeType TradeType,
        ExchangeContentBannerAssetPath BannerAssetPath,
        RemainingTimeSpan LimitTime,
        ExchangeShopEndTime EndAt)
    {
        public static ExchangeContentCellViewModel Empty { get; } =
            new ExchangeContentCellViewModel(
                MasterDataId.Empty,
                MasterDataId.Empty,
                ExchangeTradeType.NormalExchangeTrade,
                ExchangeContentBannerAssetPath.Empty,
                RemainingTimeSpan.Empty,
                ExchangeShopEndTime.Empty);
    }
}
