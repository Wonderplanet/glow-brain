namespace GLOW.Scenes.IdleIncentiveTop.Domain.ValueObjects
{
    public record EnableQuickReceiveFlag(bool Value)
    {
        public static EnableQuickReceiveFlag True { get; } = new(true);
        public static EnableQuickReceiveFlag False { get; } = new(false);

        public static implicit operator bool(EnableQuickReceiveFlag flag) => flag.Value;
    }
}
