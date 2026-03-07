using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.Home.Domain.ValueObjects
{
    public record HomeMainKomaUnitAssetPath(ObscuredString Value)
    {
        const string AssetPathFormat = "unit_image_get_{0}_02";

        public static HomeMainKomaUnitAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new HomeMainKomaUnitAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static HomeMainKomaUnitAssetPath Empty { get; } = new("");
    };
}
