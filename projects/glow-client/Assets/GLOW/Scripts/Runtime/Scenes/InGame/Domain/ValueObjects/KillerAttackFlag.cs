namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record KillerAttackFlag(bool Value)
    {
        public static KillerAttackFlag True { get; } = new(true);
        public static KillerAttackFlag False { get; } = new(false);

        public static implicit operator bool(KillerAttackFlag flag) => flag.Value;
    }
}
