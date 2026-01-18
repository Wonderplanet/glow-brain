using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record DisplayHoldingPassBannerAssetPath(ObscuredString Value)
    {
        const string AssetPathFormat = "home_pass_effect_banner_{0}";
        public static DisplayHoldingPassBannerAssetPath Empty { get; } = new (string.Empty);

        public static DisplayHoldingPassBannerAssetPath FromAssetKey(PassAssetKey passAssetKey)
        {
            return new DisplayHoldingPassBannerAssetPath(
                ZString.Format(
                    AssetPathFormat, 
                    passAssetKey.ToString()));
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}