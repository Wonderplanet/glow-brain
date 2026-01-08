using GLOW.Scenes.UnitList.Domain.ValueObjects;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterBonusModel(FilterBonusFlag EnableBonus, FilterBonusFlag BonusFilterFlag)
    {
        public static FilterBonusModel Default { get; } = new FilterBonusModel(FilterBonusFlag.False, FilterBonusFlag.False);

        public bool IsAnyFilter => EnableBonus & BonusFilterFlag;

        public bool IsOn()
        {
            return BonusFilterFlag;
        }
    }
}
