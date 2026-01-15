using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record ScoreUpdateProcessResult(
        InGameScoreModel UpdatedScoreModel,
        IReadOnlyList<ScoreCalculationResultModel> AddedScoreModels)
    {
        public static ScoreUpdateProcessResult Empty { get; } = new (
            InGameScoreModel.Empty,
            new List<ScoreCalculationResultModel>());
    }
}
