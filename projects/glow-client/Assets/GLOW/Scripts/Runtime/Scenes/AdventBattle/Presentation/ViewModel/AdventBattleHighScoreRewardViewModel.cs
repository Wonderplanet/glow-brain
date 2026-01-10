using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattle.Presentation.ViewModel
{
    public record AdventBattleHighScoreRewardViewModel(
        AdventBattleScore AdventBattleHighScore,
        PlayerResourceIconViewModel RewardViewModel,
        AdventBattleHighScoreRewardObtainedFlag ObtainedFlag,
        AdventBattleHighScoreRewardPickupFlag PickupFlag)
    {
        public static AdventBattleHighScoreRewardViewModel Empty { get; } = new(
            AdventBattleScore.Empty,
            PlayerResourceIconViewModel.Empty,
            AdventBattleHighScoreRewardObtainedFlag.False,
            AdventBattleHighScoreRewardPickupFlag.False
        );
    }
}