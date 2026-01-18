namespace GLOW.Scenes.UnitList.Domain.ValueObjects
{
    public record FilterFormationFlag(bool Value)
    {
        public static FilterFormationFlag True { get; } = new(true);
        public static FilterFormationFlag False { get; } = new(false);

        public static implicit operator bool(FilterFormationFlag flag) => flag.Value;

        public static bool operator true(FilterFormationFlag flag) => flag.Value;
        public static bool operator false(FilterFormationFlag flag) => !flag.Value;
    }
}
