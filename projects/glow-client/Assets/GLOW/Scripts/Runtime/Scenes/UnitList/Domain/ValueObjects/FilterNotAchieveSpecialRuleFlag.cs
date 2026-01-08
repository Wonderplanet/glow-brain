namespace GLOW.Scenes.UnitList.Domain.ValueObjects
{
    public record FilterNotAchieveSpecialRuleFlag(bool Value)
    {
        public static FilterNotAchieveSpecialRuleFlag True { get; } = new(true);
        public static FilterNotAchieveSpecialRuleFlag False { get; } = new(false);

        public static implicit operator bool(FilterNotAchieveSpecialRuleFlag flag) => flag.Value;

        public static bool operator true(FilterNotAchieveSpecialRuleFlag flag) => flag.Value;
        public static bool operator false(FilterNotAchieveSpecialRuleFlag flag) => !flag.Value;
    }
}
