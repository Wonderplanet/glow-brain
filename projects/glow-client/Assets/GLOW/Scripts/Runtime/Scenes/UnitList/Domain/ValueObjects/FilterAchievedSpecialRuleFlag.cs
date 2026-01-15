namespace GLOW.Scenes.UnitList.Domain.ValueObjects
{
    public record FilterAchievedSpecialRuleFlag(bool Value)
    {
        public static FilterAchievedSpecialRuleFlag True { get; } = new(true);
        public static FilterAchievedSpecialRuleFlag False { get; } = new(false);

        public static implicit operator bool(FilterAchievedSpecialRuleFlag flag) => flag.Value;

        public static bool operator true(FilterAchievedSpecialRuleFlag flag) => flag.Value;
        public static bool operator false(FilterAchievedSpecialRuleFlag flag) => !flag.Value;
    }
}
