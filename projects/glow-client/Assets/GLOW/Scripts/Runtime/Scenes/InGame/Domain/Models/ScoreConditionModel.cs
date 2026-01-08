using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ScoreConditionModel(
        ScoreConditionFlag EnemyOutpostDamageTaken)
    {
        public static ScoreConditionModel Empty { get; } = new(
            ScoreConditionFlag.Empty);
    }
}
