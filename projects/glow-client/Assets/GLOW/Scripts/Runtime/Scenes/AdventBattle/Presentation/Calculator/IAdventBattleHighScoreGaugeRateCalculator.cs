using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.AdventBattle.Presentation.Calculator.Model;

namespace GLOW.Scenes.AdventBattle.Presentation.Calculator
{
    public interface IAdventBattleHighScoreGaugeRateCalculator
    {
        AdventBattleHighScoreGaugeModel CalculateHighScoreGaugeRate(
            IReadOnlyList<AdventBattleHighScoreRewardModel> highScoreRewards,
            AdventBattleScore currentMaxScore,
            AdventBattleScore maxScoreLastAnimationPlayed);
    }
}