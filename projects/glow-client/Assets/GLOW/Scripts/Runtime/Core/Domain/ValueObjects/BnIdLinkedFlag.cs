namespace GLOW.Core.Domain.ValueObjects
{
    public record BnIdLinkedFlag(bool Value)
    {
        public static BnIdLinkedFlag True { get; } = new (true);
        public static BnIdLinkedFlag False { get; } = new (false);

        public static implicit operator bool(BnIdLinkedFlag flag) => flag.Value;
    }
}
