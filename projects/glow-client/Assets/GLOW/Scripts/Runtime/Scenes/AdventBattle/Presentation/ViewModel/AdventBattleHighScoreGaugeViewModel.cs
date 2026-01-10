using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattle.Presentation.Calculator.Model;

namespace GLOW.Scenes.AdventBattle.Presentation.ViewModel
{
    public record AdventBattleHighScoreGaugeViewModel(
        AdventBattleHighScoreGaugeRate CurrentGaugeRate,
        IReadOnlyList<AdventBattleHighScoreGaugeRateElementModel> RewardGaugeRateList)
    {
        public static AdventBattleHighScoreGaugeViewModel Empty { get; } = new AdventBattleHighScoreGaugeViewModel(
            AdventBattleHighScoreGaugeRate.Empty,
            new List<AdventBattleHighScoreGaugeRateElementModel>());
    }
}