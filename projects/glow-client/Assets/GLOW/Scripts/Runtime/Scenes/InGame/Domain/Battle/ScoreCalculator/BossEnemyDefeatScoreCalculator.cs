using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    public class BossEnemyDefeatScoreCalculator : IInGameScoreCalculator
    {
        public InGameScoreType ScoreType => InGameScoreType.BossEnemyDefeat;

        public IReadOnlyList<ScoreCalculationResultModel> CalculateScore(ScoreCalculationContext context)
        {
            var scoreModels = context.DeadUnits
                .Where(unit => unit.BattleSide == BattleSide.Enemy)
                .Where(unit => unit.IsBoss)
                .Select(unit => new ScoreCalculationResultModel(
                    unit.Id,
                    ScoreType,
                    unit.DefeatedScore))
                .ToList();

            return scoreModels;
        }
    }
}