namespace GLOW.Core.Domain.ValueObjects
{
    public record BnIdLinkableFlag(bool Value)
    {
        public static BnIdLinkableFlag True { get; } = new (true);
        public static BnIdLinkableFlag False { get; } = new (false);

        public static implicit operator bool(BnIdLinkableFlag flag) => flag.Value;
    }
}
