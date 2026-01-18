using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GachaLogoAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_logo_{0}";

        public static GachaLogoAssetPath Empty { get; } = new GachaLogoAssetPath(string.Empty);

        public static GachaLogoAssetPath FromAssetKey(GachaLogoAssetKey assetKey)
        {
            if (assetKey.IsEmpty())
            {
                return Empty;
            }
            
            return new GachaLogoAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}