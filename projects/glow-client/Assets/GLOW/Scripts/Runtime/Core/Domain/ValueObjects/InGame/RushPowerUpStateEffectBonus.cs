namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record RushPowerUpStateEffectBonus(decimal Value)
    {
        public static RushPowerUpStateEffectBonus Empty { get; } = new(0);
        public static RushPowerUpStateEffectBonus Zero { get; } = new(0);

        public static RushPowerUpStateEffectBonus operator +(RushPowerUpStateEffectBonus a, PercentageM b) => new (a.Value + b.Value);

        public PercentageM ToPercentageM() => new (Value);

        public bool IsZero()
        {
            return Value == Zero.Value;
        }
    }
}
