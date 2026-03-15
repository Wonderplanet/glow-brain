using System.Collections.Generic;

namespace GLOW.Scenes.PackShop.Domain.Models
{
    public record PackProductEvaluateModel(
        IReadOnlyList<PackShopValidateProductModel> StageClearPacks,
        IReadOnlyList<PackShopValidateProductModel> NormalPacks,
        IReadOnlyList<PackShopValidateProductModel> DailyPacks)
    {

    }
}
