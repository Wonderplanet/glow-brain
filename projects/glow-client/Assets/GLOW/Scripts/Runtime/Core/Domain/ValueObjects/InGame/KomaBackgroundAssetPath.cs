using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KomaBackgroundAssetPath(string Value)
    {
        const string AssetPathFormat = "koma_background_{0}";

        public static KomaBackgroundAssetPath Empty { get; } =  new (string.Empty);
        public static KomaBackgroundAssetPath DefaultAssetPath { get; } =  new (ZString.Format(AssetPathFormat, "glo_00001"));

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static KomaBackgroundAssetPath FromAssetKey(KomaBackgroundAssetKey assetKey)
        {
            return new KomaBackgroundAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
