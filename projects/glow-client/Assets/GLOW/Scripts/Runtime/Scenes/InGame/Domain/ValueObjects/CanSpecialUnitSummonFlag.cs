namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record CanSpecialUnitSummonFlag(bool Value)
    {
        public static CanSpecialUnitSummonFlag True { get; } = new(true);
        public static CanSpecialUnitSummonFlag False { get; } = new(false);

        public static implicit operator bool(CanSpecialUnitSummonFlag flag) => flag.Value;

        public static bool operator true(CanSpecialUnitSummonFlag flag) => flag.Value;
        public static bool operator false(CanSpecialUnitSummonFlag flag) => !flag.Value;
    }
}
