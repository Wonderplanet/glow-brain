using GLOW.Core.Domain.ValueObjects;
using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Core.Domain.Models
{
    public record MstTradeProductModel(
        MasterDataId Id,
        MasterDataId MstGroupId,
        ExchangeShopStartTime StartAt,
        ExchangeShopEndTime EndAt,
        IReadOnlyList<MstExchangeLineupModel> Lineups)
    {
    public static MstTradeProductModel Empty { get; } = new MstTradeProductModel(
        MasterDataId.Empty,
        MasterDataId.Empty,
        ExchangeShopStartTime.Empty,
        ExchangeShopEndTime.Empty,
        new List<MstExchangeLineupModel>()
    );
    }
}
