using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SeriesBannerAssetKey(ObscuredString Value)
    {
        public static SeriesBannerAssetKey Empty { get; } = new(string.Empty);
    }
}
