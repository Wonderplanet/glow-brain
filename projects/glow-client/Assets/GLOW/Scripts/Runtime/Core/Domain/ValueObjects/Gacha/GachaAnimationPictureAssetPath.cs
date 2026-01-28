using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaAnimationPictureAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_animation_koma_{0}";
        public static GachaAnimationPictureAssetPath Empty { get; } = new (string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static GachaAnimationPictureAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new GachaAnimationPictureAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
