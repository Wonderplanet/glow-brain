using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public record HomeMainBannerItemViewModel(HomeBannerAssetKey AssetKey, HomeBannerDestinationType DestinationType, HomeBannerDestinationPath DestinationPath): IRotationPageItemViewModel
    {
        public IRotationPageItemViewModel Duplicate() => new HomeMainBannerItemViewModel(AssetKey, DestinationType, DestinationPath);
    }
}
