using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    public static class ScoreAdditionTypeExtensions
    {
        public static ScoreCalculateType ToScoreCalculateType(this ScoreAdditionType type)
        {
            return type switch
            {
                ScoreAdditionType.AllEnemiesAndOutPost => ScoreCalculateType.AllEnemyUnitsAndOutPost,
                ScoreAdditionType.AllEnemies => ScoreCalculateType.AllEnemyUnits,
                ScoreAdditionType.BossEnemies => ScoreCalculateType.BossEnemyUnits,
                _ => ScoreCalculateType.None
            };
        }
        public static ScoreCalculateType ToScoreCalculateType(this ScoreAdditionType type, QuestType questType)
        {
            return questType switch
            {
                QuestType.Enhance => ScoreCalculateType.EnemyOutpost,
                _ => ScoreCalculateType.None
            };
        }
    }
}