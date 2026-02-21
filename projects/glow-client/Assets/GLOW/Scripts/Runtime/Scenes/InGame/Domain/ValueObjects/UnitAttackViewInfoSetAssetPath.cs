using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record UnitAttackViewInfoSetAssetPath(string Value)
    {
        const string AssetPathFormat = "unit_attack_view_info_set_{0}";

        public static UnitAttackViewInfoSetAssetPath Empty { get; } = new(string.Empty);

        public static UnitAttackViewInfoSetAssetPath FromAssetKey(UnitAssetKey assetKey)
        {
            return new UnitAttackViewInfoSetAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}