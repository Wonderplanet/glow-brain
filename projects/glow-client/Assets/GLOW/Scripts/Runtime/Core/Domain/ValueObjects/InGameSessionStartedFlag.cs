namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameSessionStartedFlag(bool Value)
    {
        public static InGameSessionStartedFlag True { get; } = new (true);
        public static InGameSessionStartedFlag False { get; } = new (false);

        public static implicit operator bool(InGameSessionStartedFlag flag) => flag.Value;
    }
}