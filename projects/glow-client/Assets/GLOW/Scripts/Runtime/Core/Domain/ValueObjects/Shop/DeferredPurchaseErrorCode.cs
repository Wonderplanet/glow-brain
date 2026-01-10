namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record DeferredPurchaseErrorCode(int Value)
    {
        public static DeferredPurchaseErrorCode Empty { get; } = new (-1);
        public static DeferredPurchaseErrorCode RestoreFailed { get; } = new (-2);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
