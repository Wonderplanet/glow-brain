namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record UnitTransformationFinishFlag(bool Value)
    {
        public static UnitTransformationFinishFlag True { get; } = new(true);
        public static UnitTransformationFinishFlag False { get; } = new(false);

        public static implicit operator bool(UnitTransformationFinishFlag flag) => flag.Value;
    }
}
