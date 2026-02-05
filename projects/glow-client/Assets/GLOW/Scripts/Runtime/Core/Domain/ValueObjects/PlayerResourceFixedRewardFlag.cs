namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceFixedRewardFlag(bool Value)
    {
        public static PlayerResourceFixedRewardFlag True { get; } = new PlayerResourceFixedRewardFlag(true);
        public static PlayerResourceFixedRewardFlag False { get; } = new PlayerResourceFixedRewardFlag(false);

        public static implicit operator bool(PlayerResourceFixedRewardFlag flag) => flag.Value;
    }
}