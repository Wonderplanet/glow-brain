namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record BonusFlag(bool Flg)
    {
        public static BonusFlag True { get; } = new(true);
        public static BonusFlag False { get; } = new(false);
    }
}
