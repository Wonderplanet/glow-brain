using System.Collections.Generic;

namespace GLOW.Scenes.PackShopGacha.Presentation.ViewModels
{
    public record PackShopGachaViewModel(IReadOnlyList<PackShopGachaCellViewModel> GachaCellViewModels)
    {
        public static PackShopGachaViewModel Empty { get; } = 
            new PackShopGachaViewModel(new List<PackShopGachaCellViewModel>());
    }
}