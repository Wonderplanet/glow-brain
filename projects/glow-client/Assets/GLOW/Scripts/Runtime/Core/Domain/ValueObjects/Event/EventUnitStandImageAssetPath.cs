using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventUnitStandImageAssetPath(string Value)
    {
        const string AssetPathPrefix = "unit_image_rush_{0}";
        public static EventUnitStandImageAssetPath Empty { get; } = new EventUnitStandImageAssetPath(string.Empty);

        public static EventUnitStandImageAssetPath FromAssetKey(UnitAssetKey key) => new EventUnitStandImageAssetPath(ZString.Format(AssetPathPrefix, key.Value));
        public bool IsEmpty() => ReferenceEquals(this, Empty);

    };
}
