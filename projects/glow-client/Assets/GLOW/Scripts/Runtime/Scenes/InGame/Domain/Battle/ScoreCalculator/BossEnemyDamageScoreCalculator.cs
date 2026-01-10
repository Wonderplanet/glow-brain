using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    public class BossEnemyDamageScoreCalculator : IInGameScoreCalculator
    {
        public InGameScoreType ScoreType => InGameScoreType.Damage;

        public IReadOnlyList<ScoreCalculationResultModel> CalculateScore(ScoreCalculationContext context)
        {
            // 死亡したユニットも含める
            var bossIds = context.Units
                .Concat(context.DeadUnits)
                .Where(unit => unit.IsBoss)
                .Where(unit => unit.BattleSide == BattleSide.Enemy)
                .Select(unit => unit.Id)
                .ToHashSet();

            var scoreModels = context.AppliedAttackResults
                .Where(result => bossIds.Contains(result.TargetId))
                .Select(result => new ScoreCalculationResultModel(
                    result.TargetId,
                    ScoreType,
                    result.AppliedDamage.ToInGameScore(context.DamageScoreAdditionalCoef)))
                .ToList();

            return scoreModels;
        }
    }
}
