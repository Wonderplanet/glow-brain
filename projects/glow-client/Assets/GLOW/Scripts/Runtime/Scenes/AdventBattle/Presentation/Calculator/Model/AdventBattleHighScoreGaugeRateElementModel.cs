using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattle.Presentation.ValueObject;

namespace GLOW.Scenes.AdventBattle.Presentation.Calculator.Model
{
    public record AdventBattleHighScoreGaugeRateElementModel(
        HighScoreRewardCellIndex HighScoreRewardCellIndex,
        AdventBattleHighScoreGaugeRate AdventBattleHighScoreGaugeRate,
        AdventBattleHighScoreRewardObtainedFlag AdventBattleHighScoreRewardObtainedFlag)
    {
        public static AdventBattleHighScoreGaugeRateElementModel Empty { get; } = new AdventBattleHighScoreGaugeRateElementModel(
            HighScoreRewardCellIndex.Empty,
            AdventBattleHighScoreGaugeRate.Empty,
            AdventBattleHighScoreRewardObtainedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}