using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record RushUnitImageAssetPath(string Value)
    {
        const string AssetPathFormat = "unit_image_rush_{0}";

        public static RushUnitImageAssetPath Empty { get; } = new RushUnitImageAssetPath(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static RushUnitImageAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new RushUnitImageAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
