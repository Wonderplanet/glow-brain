using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.PackShopGacha.Domain.Models;
using GLOW.Scenes.PackShopGacha.Presentation.ViewModels;

namespace GLOW.Scenes.PackShopGacha.Presentation.Translators
{
    public static class PackShopGachaViewModelTranslator
    {
        public static PackShopGachaViewModel Translate(PackShopGachaUseCaseModel useCaseModel)
        {
            return new PackShopGachaViewModel(
                useCaseModel.PackShopGachaBannerModels
                    .Select(Translate)
                    .ToList());
        }
        
        static PackShopGachaCellViewModel Translate(PackShopGachaBannerModel bannerModel)
        {
            return new PackShopGachaCellViewModel(
                bannerModel.GachaId,
                bannerModel.PackShopGachaBannerAssetPath);
        }
    }
}