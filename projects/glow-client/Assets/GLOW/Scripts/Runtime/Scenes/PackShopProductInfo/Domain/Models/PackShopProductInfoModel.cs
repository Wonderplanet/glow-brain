using System.Collections.Generic;

namespace GLOW.Scenes.PackShopProductInfo.Domain.Models
{
    public record PackShopProductInfoModel(
        IReadOnlyList<PackShopProductInfoContentModel> ContentModels,
        IReadOnlyList<PackShopProductInfoContentModel> BonusModels);
}
