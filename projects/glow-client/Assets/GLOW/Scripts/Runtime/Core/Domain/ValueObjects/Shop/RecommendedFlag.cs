namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record RecommendedFlag(bool Flg)
    {
        public static RecommendedFlag False { get; } = new(false);
        public static RecommendedFlag True { get; } = new(true);
    }
}
