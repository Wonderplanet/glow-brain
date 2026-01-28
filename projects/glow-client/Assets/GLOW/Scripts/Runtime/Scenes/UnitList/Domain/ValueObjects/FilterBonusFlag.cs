namespace GLOW.Scenes.UnitList.Domain.ValueObjects
{
    public record FilterBonusFlag(bool Value)
    {
        public static FilterBonusFlag True { get; } = new(true);
        public static FilterBonusFlag False { get; } = new(false);

        public static implicit operator bool(FilterBonusFlag flag) => flag.Value;

        public static bool operator true(FilterBonusFlag flag) => flag.Value;
        public static bool operator false(FilterBonusFlag flag) => !flag.Value;
    }
}
