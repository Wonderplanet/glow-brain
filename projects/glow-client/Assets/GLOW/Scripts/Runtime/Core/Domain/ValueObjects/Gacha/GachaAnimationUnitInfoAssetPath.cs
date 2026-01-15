namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaAnimationUnitInfoAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_animation_unit_info_{0}";

        public static GachaAnimationUnitInfoAssetPath Empty { get; } = new(string.Empty);

        public static GachaAnimationUnitInfoAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new GachaAnimationUnitInfoAssetPath(string.Format(AssetPathFormat, assetKey.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

