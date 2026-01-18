using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record ResultSpeedAttackRewardModel(
        PlayerResourceModel RewardIcon,
        StageClearTime UpperClearTimeMs,
        AcquiredRewardFlag IsAcquired,
        NewRewardFlag IsNew)
    {
        public static ResultSpeedAttackRewardModel Empty { get; } = new(
            PlayerResourceModel.Empty,
            StageClearTime.Empty,
            AcquiredRewardFlag.False,
            NewRewardFlag.False
        );
    }
}
