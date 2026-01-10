namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record ScoreEffectVisibleFlag(bool Value)
    {
        public static ScoreEffectVisibleFlag True { get; } = new(true);
        public static ScoreEffectVisibleFlag False { get; } = new(false);
        public static ScoreEffectVisibleFlag Empty { get; } = new(false);

        public static bool operator true(ScoreEffectVisibleFlag visibleFlag) => visibleFlag.Value;
        public static bool operator false(ScoreEffectVisibleFlag visibleFlag) => !visibleFlag.Value;
    }
}
