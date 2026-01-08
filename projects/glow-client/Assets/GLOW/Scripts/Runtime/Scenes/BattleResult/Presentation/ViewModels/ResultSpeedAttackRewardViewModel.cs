using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record ResultSpeedAttackRewardViewModel(
        PlayerResourceIconViewModel RewardIcon,
        StageClearTime UpperClearTimeMs,
        AcquiredRewardFlag IsAcquired,
        NewRewardFlag IsNew);
}
