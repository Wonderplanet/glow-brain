namespace GLOW.Core.Domain.ValueObjects
{
    public record DisplayTryStageTextFlag(bool Value)
    {
        public static DisplayTryStageTextFlag True { get; } = new DisplayTryStageTextFlag(true);
        public static DisplayTryStageTextFlag False { get; } = new DisplayTryStageTextFlag(false);

        public static implicit operator bool(DisplayTryStageTextFlag textFlag) => textFlag.Value;
    }
}