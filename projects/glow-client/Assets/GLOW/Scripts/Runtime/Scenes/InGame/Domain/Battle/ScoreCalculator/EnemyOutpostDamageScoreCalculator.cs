using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    public class EnemyOutpostDamageScoreCalculator : IInGameScoreCalculator
    {
        public InGameScoreType ScoreType => InGameScoreType.Damage;

        public IReadOnlyList<ScoreCalculationResultModel> CalculateScore(ScoreCalculationContext context)
        {
            var scoreModels = context.AppliedAttackResults
                .Where(result => result.TargetId == context.EnemyOutpost.Id)
                .Select(result => new ScoreCalculationResultModel(
                    result.TargetId,
                    ScoreType,
                    result.Damage.ToInGameScore(context.DamageScoreAdditionalCoef)))
                .ToList();

            return scoreModels;
        }
    }
}
