using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.ViewModels
{
    public record EnhanceQuestScoreDetailCellViewModel(
        EnhanceQuestMinThresholdScore EnhanceQuestMinThresholdScore,
        ItemAmount CoinRewardAmount,
        CoinRewardSizeType CoinRewardSizeType);
}
