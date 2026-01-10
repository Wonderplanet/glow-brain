namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceAcquiredFlag(bool Value)
    {
        public static PlayerResourceAcquiredFlag True { get; } = new PlayerResourceAcquiredFlag(true);
        public static PlayerResourceAcquiredFlag False { get; } = new PlayerResourceAcquiredFlag(false);

        public static implicit operator bool(PlayerResourceAcquiredFlag flag) => flag.Value;
    }
}