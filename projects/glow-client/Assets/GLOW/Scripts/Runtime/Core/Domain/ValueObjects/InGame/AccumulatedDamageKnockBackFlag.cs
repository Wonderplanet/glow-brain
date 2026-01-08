namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AccumulatedDamageKnockBackFlag(bool Value)
    {
        public static AccumulatedDamageKnockBackFlag True { get; } = new(true);
        public static AccumulatedDamageKnockBackFlag False { get; } = new(false);

        public static implicit operator bool(AccumulatedDamageKnockBackFlag flag) => flag.Value;
    }
}