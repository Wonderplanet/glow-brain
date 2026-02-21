using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GachaContentCutInAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_top_cutin_{0}";
        
        public static GachaContentCutInAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new GachaContentCutInAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
