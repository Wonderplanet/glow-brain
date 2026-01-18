namespace GLOW.Core.Domain.ValueObjects
{
    public record MaxStatusFlag(bool Value)
    {
        public static MaxStatusFlag Empty { get; } = new MaxStatusFlag(false);
        public static MaxStatusFlag True { get; } = new MaxStatusFlag(true);
        public static MaxStatusFlag False { get; } = new MaxStatusFlag(false);

        public static implicit operator bool(MaxStatusFlag flag) => flag.Value;
    }
}
