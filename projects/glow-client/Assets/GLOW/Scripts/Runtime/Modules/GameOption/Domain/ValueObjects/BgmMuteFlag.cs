namespace GLOW.Modules.GameOption.Domain.ValueObjects
{
    public record BgmMuteFlag(bool Value)
    {
        public static BgmMuteFlag True { get; } = new(true);
        public static BgmMuteFlag False { get; } = new(false);

        public static implicit operator bool(BgmMuteFlag flag) => flag.Value;
        public static BgmMuteFlag operator !(BgmMuteFlag flag) => new(!flag.Value);
    }
}