using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Domain.Models
{
    public record EnhanceQuestScoreDetailCellModel(
        EnhanceQuestMinThresholdScore EnhanceQuestMinThresholdScore,
        ItemAmount CoinRewardAmount,
        CoinRewardSizeType CoinRewardSizeType);
}
