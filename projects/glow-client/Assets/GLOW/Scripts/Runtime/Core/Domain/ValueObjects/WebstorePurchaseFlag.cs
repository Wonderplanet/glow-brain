namespace GLOW.Core.Domain.ValueObjects
{
    public record WebstorePurchaseFlag(bool Value)
    {
        public static WebstorePurchaseFlag True { get; } = new(true);
        public static WebstorePurchaseFlag False { get; } = new(false);

        public static implicit operator bool(WebstorePurchaseFlag flag) => flag.Value;
    }
}
