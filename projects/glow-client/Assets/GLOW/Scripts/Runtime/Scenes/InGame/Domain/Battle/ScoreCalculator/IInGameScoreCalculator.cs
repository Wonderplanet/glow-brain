using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator
{
    // ScoreCalculateType単位で実体が作られる(+Empty)
    // 1実体を1要素として、複数使うときもある
    public interface IInGameScoreCalculator
    {
        InGameScoreType ScoreType { get; }
        IReadOnlyList<ScoreCalculationResultModel> CalculateScore(ScoreCalculationContext context);
    }
}
