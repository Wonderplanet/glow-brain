namespace GLOW.Core.Domain.ValueObjects
{
    public record AcquiredRewardFlag(bool Value)
    {
        public static AcquiredRewardFlag True { get; } = new AcquiredRewardFlag(true);
        public static AcquiredRewardFlag False { get; } = new AcquiredRewardFlag(false);

        public static implicit operator bool(AcquiredRewardFlag acquiredRewardFlag) => acquiredRewardFlag.Value;
    }
}
