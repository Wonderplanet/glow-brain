namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record HealPower(decimal Value)
    {
        public static HealPower Empty { get; } = new(0);
        public static HealPower Default => new(100);

        public static HealPower FromPercentageM(PercentageM value) => new(value.Value);

        public decimal ToRate() => Value / 100m;
    }
}
