using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public static class HomeMainBannerItemViewModelTranslator
    {
        public static HomeMainBannerItemViewModel Translate(HomeMainBannerUseCaseModel data)
        {
            return new HomeMainBannerItemViewModel(data.AssetKey, data.DestinationType, data.DestinationPath);
        }
    }
}
