namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record UnitActionStartFlag(bool Value)
    {
        public static UnitActionStartFlag True { get; } = new(true);
        public static UnitActionStartFlag False { get; } = new(false);

        public static implicit operator bool(UnitActionStartFlag flag) => flag.Value;
    }
}
