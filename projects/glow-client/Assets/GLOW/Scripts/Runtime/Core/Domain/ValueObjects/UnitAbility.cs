using System.Text.RegularExpressions;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitAbility(
        UnitAbilityType Type,
        UnitAbilityAssetKey AssetKey,
        UnitAbilityParameter Parameter1,
        UnitAbilityParameter Parameter2,
        UnitAbilityParameter Parameter3,
        ObscuredString Description,
        UnitRank UnlockUnitRank)
    {
        public static UnitAbility Empty { get; } = new(
            UnitAbilityType.None,
            UnitAbilityAssetKey.Empty,
            UnitAbilityParameter.Empty,
            UnitAbilityParameter.Empty,
            UnitAbilityParameter.Empty,
            string.Empty,
            UnitRank.Empty);

        public UnitAbilityIconAssetPath AbilityIconPath => UnitAbilityIconAssetPath.FromAssetKey(AssetKey);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
