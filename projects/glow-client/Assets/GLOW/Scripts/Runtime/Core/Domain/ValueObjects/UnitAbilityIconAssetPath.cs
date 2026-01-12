using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitAbilityIconAssetPath(string Value)
    {
        const string AssetPathFormat = "unit_ability_icon_{0}";

        public static UnitAbilityIconAssetPath Empty = new UnitAbilityIconAssetPath(string.Empty);

        public static UnitAbilityIconAssetPath FromAssetKey(UnitAbilityAssetKey assetKey)
        {
            return new UnitAbilityIconAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static string FromAssetKeyToString(UnitAbilityAssetKey assetKey)
        {
            return ZString.Format(AssetPathFormat, assetKey.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
