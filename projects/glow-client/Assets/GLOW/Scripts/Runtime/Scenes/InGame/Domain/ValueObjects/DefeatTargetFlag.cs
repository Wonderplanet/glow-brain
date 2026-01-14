namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record DefeatTargetFlag(bool Value)
    {
        public static DefeatTargetFlag True { get; } = new(true);
        public static DefeatTargetFlag False { get; } = new(false);

        public static implicit operator bool(DefeatTargetFlag flag) => flag.Value;
    }
}