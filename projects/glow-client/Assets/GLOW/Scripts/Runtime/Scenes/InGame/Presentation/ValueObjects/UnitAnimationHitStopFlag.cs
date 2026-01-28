namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record UnitAnimationHitStopFlag(bool Value)
    {
        public static UnitAnimationHitStopFlag True { get; } = new(true);
        public static UnitAnimationHitStopFlag False { get; } = new(false);

        public static implicit operator bool(UnitAnimationHitStopFlag flag) => flag.Value;
    }
}
