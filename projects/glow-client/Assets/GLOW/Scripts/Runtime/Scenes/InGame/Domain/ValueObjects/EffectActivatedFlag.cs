namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record EffectActivatedFlag(bool Value)
    {
        public static EffectActivatedFlag True { get; } = new(true);
        public static EffectActivatedFlag False { get; } = new(false);

        public static implicit operator bool(EffectActivatedFlag flag) => flag.Value;
    }
}
