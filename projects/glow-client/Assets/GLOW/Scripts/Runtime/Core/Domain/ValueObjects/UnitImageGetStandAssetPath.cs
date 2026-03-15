using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitImageGetStandAssetPath(string Value)
    {
        const string StandAssetPathFormat = "unit_image_get_{0}_01";

        public static UnitImageGetStandAssetPath Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static UnitImageGetStandAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new UnitImageGetStandAssetPath(ZString.Format(StandAssetPathFormat, assetKey.Value));
        }
    }
}