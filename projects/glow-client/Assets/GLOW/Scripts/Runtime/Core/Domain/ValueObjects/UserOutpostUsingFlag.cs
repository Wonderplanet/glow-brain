namespace GLOW.Core.Domain.ValueObjects
{
    public record UserOutpostUsingFlag(bool Value)
    {
        public static UserOutpostUsingFlag True { get; } = new (true);
        public static UserOutpostUsingFlag False { get; } = new (false);

        public static implicit operator bool(UserOutpostUsingFlag flag) => flag.Value;
    }
}