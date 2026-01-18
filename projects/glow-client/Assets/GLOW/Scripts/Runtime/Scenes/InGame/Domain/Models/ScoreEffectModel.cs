using System.Collections.Generic;

namespace GLOW.Scenes.InGame.Domain.Models
{
    /// <summary> スコアエフェクトの表示用Model </summary>
    public record ScoreEffectModel(IReadOnlyList<ScoreCalculationResultModel> ScoreModels)
    {
        public static ScoreEffectModel Empty { get; } = new (
            new List<ScoreCalculationResultModel>());
    }
}
