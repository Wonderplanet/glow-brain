namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StagePlayableFlag(bool Value)
    {
        public static StagePlayableFlag False { get; } = new StagePlayableFlag(false);
        public static StagePlayableFlag True { get; } = new StagePlayableFlag(true);
        public static implicit operator bool(StagePlayableFlag flag) => flag.Value;
    };
}
