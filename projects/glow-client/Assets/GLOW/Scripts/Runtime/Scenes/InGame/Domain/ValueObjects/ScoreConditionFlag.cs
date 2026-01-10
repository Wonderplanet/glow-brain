namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record ScoreConditionFlag(bool Value)
    {
        public static ScoreConditionFlag Empty { get; } = new(false);
        public static ScoreConditionFlag True { get; } = new(true);
        public static ScoreConditionFlag False { get; } = new(false);

        public static implicit operator bool(ScoreConditionFlag flag) => flag.Value;
    }
}
