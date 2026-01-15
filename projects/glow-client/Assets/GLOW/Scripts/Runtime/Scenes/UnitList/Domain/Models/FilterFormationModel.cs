using GLOW.Scenes.UnitList.Domain.ValueObjects;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterFormationModel(
        FilterFormationFlag EnableFormationFlag,
        FilterAchievedSpecialRuleFlag IsFilterAchievedSpecialRuleFlag,
        FilterNotAchieveSpecialRuleFlag IsFilterNotAchieveSpecialRuleFlag)
    {
        public static FilterFormationModel Default { get; } = new FilterFormationModel(
            FilterFormationFlag.False,
            FilterAchievedSpecialRuleFlag.False,
            FilterNotAchieveSpecialRuleFlag.False);

        public bool IsAnyFilter => EnableFormationFlag && (IsFilterAchievedSpecialRuleFlag || IsFilterNotAchieveSpecialRuleFlag);

        public bool IsOn()
        {
            return IsFilterAchievedSpecialRuleFlag || IsFilterNotAchieveSpecialRuleFlag;
        }
    }
}
