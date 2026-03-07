using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class ScoreUpdateProcess : IScoreUpdateProcess
    {
        public ScoreUpdateProcessResult UpdateScore(
            InGameScoreModel scoreModel,
            ScoreCalculateModel scoreCalculateModel,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            OutpostModel enemyOutpost,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults)
        {
            if (scoreCalculateModel.Calculators.Count == 0) return new ScoreUpdateProcessResult(scoreModel, new List<ScoreCalculationResultModel>());

            var context = new ScoreCalculationContext(
                units,
                deadUnits,
                enemyOutpost,
                appliedAttackResults,
                scoreCalculateModel.DamageScoreAdditionalCoef);

            var additionalScores = scoreCalculateModel.Calculators
                .SelectMany(calculator => calculator.CalculateScore(context))
                .ToList();

            var updatedScoreModel = scoreModel.AddScore(additionalScores);

            return new ScoreUpdateProcessResult(
                updatedScoreModel,
                additionalScores);
        }
    }
}
