using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    public record ScoreCalculationContext(
        IReadOnlyList<CharacterUnitModel> Units,
        IReadOnlyList<CharacterUnitModel> DeadUnits,
        OutpostModel EnemyOutpost,
        IReadOnlyList<AppliedAttackResultModel> AppliedAttackResults,
        DamageScoreAdditionalCoef DamageScoreAdditionalCoef)
    {
        public static ScoreCalculationContext Empty { get; } = new(
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            OutpostModel.Empty,
            new List<AppliedAttackResultModel>(),
            DamageScoreAdditionalCoef.Empty);
    }
}
