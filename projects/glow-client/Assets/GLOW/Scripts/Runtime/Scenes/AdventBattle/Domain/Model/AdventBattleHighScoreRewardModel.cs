using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattle.Domain.Model
{
    public record AdventBattleHighScoreRewardModel(
        AdventBattleScore AdventBattleHighScore,
        PlayerResourceModel Reward,
        AdventBattleHighScoreRewardObtainedFlag RewardObtainedFlag,
        AdventBattleHighScoreRewardPickupFlag RewardPickupFlag)
    {
        public static AdventBattleHighScoreRewardModel Empty { get; } = new AdventBattleHighScoreRewardModel(
            AdventBattleScore.Empty,
            PlayerResourceModel.Empty,
            AdventBattleHighScoreRewardObtainedFlag.False,
            AdventBattleHighScoreRewardPickupFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
