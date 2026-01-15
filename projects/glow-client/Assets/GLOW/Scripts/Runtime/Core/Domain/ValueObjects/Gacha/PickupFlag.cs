namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record PickupFlag(bool Value)
    {
        public static PickupFlag Empty { get; } = new(false);
    }
}
