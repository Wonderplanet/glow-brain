namespace GLOW.Core.Domain.ValueObjects
{
    public record PhantomizedFlag(bool Value)
    {
        public static PhantomizedFlag True { get; } = new(true);
        public static PhantomizedFlag False { get; } = new(false);

        public static implicit operator bool(PhantomizedFlag flag) => flag.Value;
    }
}