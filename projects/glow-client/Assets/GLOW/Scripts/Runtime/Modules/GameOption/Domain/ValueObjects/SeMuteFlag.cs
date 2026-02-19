namespace GLOW.Modules.GameOption.Domain.ValueObjects
{
    public record SeMuteFlag(bool Value)
    {
        public static SeMuteFlag True { get; } = new(true);
        public static SeMuteFlag False { get; } = new(false);

        public static implicit operator bool(SeMuteFlag flag) => flag.Value;
        public static SeMuteFlag operator !(SeMuteFlag flag) => new(!flag.Value);
    }
}