namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record IsNewUnitBadge(bool Value)
    {
        public static IsNewUnitBadge Empty { get; } = new(false);
    }
}
