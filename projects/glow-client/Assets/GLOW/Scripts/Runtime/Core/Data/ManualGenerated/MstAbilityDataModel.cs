using GLOW.Core.Data.Data;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.ManualGenerated
{
    public record MstAbilityDataModel(
        MstUnitAbilityData UnitAbility,
        MstAbilityData Ability,
        MstAbilityI18nData AbilityI18n,
        UnitRank UnlockUnitRank)
    {
        public static MstAbilityDataModel Empty => new(
            null,
            null,
            null,
            UnitRank.Empty);
    }
}
