namespace GLOW.Modules.GameOption.Domain.ValueObjects
{
    public record CanGiveUpFlag(bool Value)
    {
        public static CanGiveUpFlag True { get; } = new(true);
        public static CanGiveUpFlag False { get; } = new(false);

        public static implicit operator bool(CanGiveUpFlag flag) => flag.Value;
        public static CanGiveUpFlag operator !(CanGiveUpFlag flag) => new(!flag.Value);
    }
}
