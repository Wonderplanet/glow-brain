namespace GLOW.Core.Domain.ValueObjects
{
    public record NewRewardFlag(bool Value)
    {
        public static NewRewardFlag True { get; } = new NewRewardFlag(true);
        public static NewRewardFlag False { get; } = new NewRewardFlag(false);

        public static implicit operator bool(NewRewardFlag flag) => flag.Value;
}
}
