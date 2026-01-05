namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record SpecialUnitSummonPositionSelectingFlag(bool Value)
    {
        public static SpecialUnitSummonPositionSelectingFlag True { get; } = new(true);
        public static SpecialUnitSummonPositionSelectingFlag False { get; } = new(false);

        public static implicit operator bool(SpecialUnitSummonPositionSelectingFlag flag) => flag.Value;
    }
}
