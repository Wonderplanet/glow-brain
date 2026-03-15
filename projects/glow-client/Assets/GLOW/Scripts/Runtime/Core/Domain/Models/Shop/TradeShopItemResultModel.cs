using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Shop
{
    public record TradeShopItemResultModel(
        IReadOnlyList<UserShopItemModel> UserShopItemModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserItemModel> UserItemModels);
}