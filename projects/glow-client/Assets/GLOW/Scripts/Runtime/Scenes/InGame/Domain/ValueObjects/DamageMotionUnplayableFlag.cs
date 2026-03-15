namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DamageMotionUnplayableFlag(bool Value)
    {
        public static DamageMotionUnplayableFlag True { get; } = new(true);
        public static DamageMotionUnplayableFlag False { get; } = new(false);

        public static implicit operator bool(DamageMotionUnplayableFlag flag) => flag.Value;
    }
}