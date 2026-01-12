namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AdvantageUnitColorFlag(bool Value)
    {
        public static AdvantageUnitColorFlag True { get; } = new(true);
        public static AdvantageUnitColorFlag False { get; } = new(false);

        public static implicit operator bool(AdvantageUnitColorFlag flag) => flag.Value;
    }
}
