using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    /// <summary>
    /// ボス以外の敵撃破スコア計算
    /// </summary>
    public class EnemyExceptBossDefeatScoreCalculator : IInGameScoreCalculator
    {
        public InGameScoreType ScoreType => InGameScoreType.EnemyDefeat;

        public IReadOnlyList<ScoreCalculationResultModel> CalculateScore(ScoreCalculationContext context)
        {
            var scoreModels = context.DeadUnits
                .Where(unit => unit.BattleSide == BattleSide.Enemy)
                .Where(unit => !unit.IsBoss)
                .Select(unit => new ScoreCalculationResultModel(
                    unit.Id,
                    ScoreType,
                    unit.DefeatedScore))
                .ToList();

            return scoreModels;
        }
    }
}