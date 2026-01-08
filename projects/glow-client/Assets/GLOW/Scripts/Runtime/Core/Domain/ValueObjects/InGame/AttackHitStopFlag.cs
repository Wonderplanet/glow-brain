namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackHitStopFlag(bool Value)
    {
        public static AttackHitStopFlag True { get; } = new(true);
        public static AttackHitStopFlag False { get; } = new(false);

        public static implicit operator bool(AttackHitStopFlag flag) => flag.Value;
    }
}