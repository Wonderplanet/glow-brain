using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainBannerUseCaseModel(
        HomeBannerAssetKey AssetKey,
        HomeBannerDestinationType DestinationType,
        HomeBannerDestinationPath DestinationPath);
}
