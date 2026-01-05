namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record UnitAnimationLoopFlag(bool Value)
    {
        public static UnitAnimationLoopFlag True { get; } = new(true);
        public static UnitAnimationLoopFlag False { get; } = new(false);

        public static implicit operator bool(UnitAnimationLoopFlag flag) => flag.Value;
    }
}
