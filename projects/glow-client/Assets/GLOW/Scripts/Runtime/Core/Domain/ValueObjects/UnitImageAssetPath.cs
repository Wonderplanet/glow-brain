using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitImageAssetPath(string Value)
    {
        const string AssetPathFormat = "unit_{0}";

        public static UnitImageAssetPath Empty { get; } = new UnitImageAssetPath(string.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public static UnitImageAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new UnitImageAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
