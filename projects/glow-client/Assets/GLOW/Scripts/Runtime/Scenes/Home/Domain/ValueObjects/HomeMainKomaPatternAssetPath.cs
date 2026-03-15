using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record HomeMainKomaPatternAssetPath(ObscuredString Value)
    {
        const string AssetPathFormat = "home_main_koma_pattern_{0}";

        public static HomeMainKomaPatternAssetPath FromAssetKey(HomeMainKomaPatternAssetKey assetKey)
        {
            return new HomeMainKomaPatternAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static HomeMainKomaPatternAssetPath Empty { get; } = new HomeMainKomaPatternAssetPath(string.Empty);
    };
}
