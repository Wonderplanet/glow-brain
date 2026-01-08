using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShop.Domain.Models
{
    public record PackShopProductListModel(
        IReadOnlyList<PackShopProductModel> NormalPacks,
        IReadOnlyList<PackShopProductModel> DailyPacks,
        IReadOnlyList<PackShopProductModel> StageClearPacks,
        RemainingTimeSpan RemainingDailyPackTime);
}
