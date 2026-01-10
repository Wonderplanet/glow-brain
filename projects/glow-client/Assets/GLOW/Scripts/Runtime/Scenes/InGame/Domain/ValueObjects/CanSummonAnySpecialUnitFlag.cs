namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record CanSummonAnySpecialUnitFlag(bool Value)
    {
        public static CanSummonAnySpecialUnitFlag True { get; } = new(true);
        public static CanSummonAnySpecialUnitFlag False { get; } = new(false);

        public static implicit operator bool(CanSummonAnySpecialUnitFlag flag) => flag.Value;

        public static bool operator true(CanSummonAnySpecialUnitFlag flag) => flag.Value;
        public static bool operator false(CanSummonAnySpecialUnitFlag flag) => !flag.Value;
        public static CanSummonAnySpecialUnitFlag operator !(CanSummonAnySpecialUnitFlag flag) => new(!flag.Value);
    }
}
