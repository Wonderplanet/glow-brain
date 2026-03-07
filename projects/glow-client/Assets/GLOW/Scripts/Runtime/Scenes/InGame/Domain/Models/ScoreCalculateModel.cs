using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record ScoreCalculateModel(
        IReadOnlyList<IInGameScoreCalculator> Calculators,
        DamageScoreAdditionalCoef DamageScoreAdditionalCoef)
    {
        public static ScoreCalculateModel Empty { get; } = new(
            new List<IInGameScoreCalculator>(),
            DamageScoreAdditionalCoef.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
