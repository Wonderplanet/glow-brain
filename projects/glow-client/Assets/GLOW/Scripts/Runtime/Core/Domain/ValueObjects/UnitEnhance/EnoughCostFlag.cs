namespace GLOW.Core.Domain.ValueObjects
{
    public record EnoughCostFlag(bool Value)
    {
        public static EnoughCostFlag True { get; } = new(true);
        public static EnoughCostFlag False { get; } = new(false);

        public static implicit operator bool(EnoughCostFlag flag) => flag.Value;
    }
}
