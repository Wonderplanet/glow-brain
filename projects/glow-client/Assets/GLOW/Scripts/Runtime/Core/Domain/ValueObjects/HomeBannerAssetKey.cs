using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record HomeBannerAssetKey(ObscuredString Value)
    {
        public static HomeBannerAssetKey Empty { get; } = new(string.Empty);
    };
}
