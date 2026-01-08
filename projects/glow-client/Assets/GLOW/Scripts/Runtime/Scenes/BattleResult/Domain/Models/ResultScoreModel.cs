using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record ResultScoreModel(
        InGameScore CurrentScore,
        InGameScore HighScore,
        NewRecordFlag NewRecordFlag,
        EventBonusPercentage TotalBonusPercentage)
    {
        public static ResultScoreModel Empty { get; } = new ResultScoreModel(
            InGameScore.Empty,
            InGameScore.Empty,
            NewRecordFlag.Empty,
            EventBonusPercentage.Empty
        );
    }
}
