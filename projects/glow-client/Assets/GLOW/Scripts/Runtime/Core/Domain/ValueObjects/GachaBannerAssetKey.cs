using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GachaBannerAssetKey(ObscuredString Value)
    {
        public static GachaBannerAssetKey Empty { get; } = new GachaBannerAssetKey(string.Empty);

        public bool IsEmpty()
        {
              return Value == Empty.Value;
        }
    }
}
