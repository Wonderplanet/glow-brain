using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GachaLogoAssetKey(ObscuredString Value)
    {
        public static GachaLogoAssetKey Empty { get; } = new GachaLogoAssetKey(string.Empty);

        public bool IsEmpty()
        {
            return Value == Empty.Value;
        }
    }
}