namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record UnitAnimationHoldAtEndFlag(bool Value)
    {
        public static UnitAnimationHoldAtEndFlag True { get; } = new(true);
        public static UnitAnimationHoldAtEndFlag False { get; } = new(false);

        public static implicit operator bool(UnitAnimationHoldAtEndFlag flag) => flag.Value;
    }
}
