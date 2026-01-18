using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostEnhanceIconAssetPath(string Value)
    {
        static string AssetPathFormat => "gate_strengthening_icon_{0}";

        public static OutpostEnhanceIconAssetPath FromAssetKey(OutpostEnhanceIconAssetKey assetKey)
        {
            return new OutpostEnhanceIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
