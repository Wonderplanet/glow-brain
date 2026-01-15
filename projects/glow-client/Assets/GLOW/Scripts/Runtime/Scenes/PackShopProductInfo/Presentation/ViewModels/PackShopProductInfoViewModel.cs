using System.Collections.Generic;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.ViewModels
{
    public record PackShopProductInfoViewModel(
        IReadOnlyList<PackShopProductInfoContentViewModel> Contents,
        IReadOnlyList<PackShopProductInfoContentViewModel> Bonuses);
}
