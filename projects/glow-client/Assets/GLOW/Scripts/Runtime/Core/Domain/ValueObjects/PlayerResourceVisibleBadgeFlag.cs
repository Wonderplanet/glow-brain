namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceVisibleBadgeFlag(bool Value)
    {
        public static PlayerResourceVisibleBadgeFlag True { get; } = new PlayerResourceVisibleBadgeFlag(true);
        public static PlayerResourceVisibleBadgeFlag False { get; } = new PlayerResourceVisibleBadgeFlag(false);

        public static implicit operator bool(PlayerResourceVisibleBadgeFlag flag) => flag.Value;
    }
}