using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattle.Presentation.Calculator.Model
{
    public record AdventBattleHighScoreGaugeModel(
        AdventBattleHighScoreGaugeRate CurrentGaugeRate,
        IReadOnlyList<AdventBattleHighScoreGaugeRateElementModel> RewardGaugeRateList)
    {
        public static AdventBattleHighScoreGaugeModel Empty { get; } = new AdventBattleHighScoreGaugeModel(
            AdventBattleHighScoreGaugeRate.Empty,
            new List<AdventBattleHighScoreGaugeRateElementModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}