using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaAnimationFukidashiAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_animation_fukidashi_{0}";
        public static GachaAnimationFukidashiAssetPath Empty { get; } = new (string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static GachaAnimationFukidashiAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new GachaAnimationFukidashiAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
