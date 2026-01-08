using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShop.Presentation.ViewModels
{
    public record PackShopProductListViewModel(
        IReadOnlyList<PackShopProductViewModel> NormalPacks,
        IReadOnlyList<PackShopProductViewModel> DailyPacks,
        IReadOnlyList<PackShopProductViewModel> StageClearPacks,
        RemainingTimeSpan RemainingDailyPackTime);
}
