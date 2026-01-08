using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ScoreCalculationResultModel(
        FieldObjectId SourceFieldObjectId,
        InGameScoreType ScoreType,
        InGameScore Score)
    {
        public static ScoreCalculationResultModel Empty { get; } = new ScoreCalculationResultModel(
            FieldObjectId.Empty,
            InGameScoreType.Damage,
            InGameScore.Empty);
    }
}
