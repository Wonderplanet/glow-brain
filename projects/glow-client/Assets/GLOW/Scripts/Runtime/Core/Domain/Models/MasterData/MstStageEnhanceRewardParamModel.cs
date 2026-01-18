using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record MstStageEnhanceRewardParamModel(
        MasterDataId Id,
        EnhanceQuestMinThresholdScore MinThresholdScore,
        ItemAmount CoinRewardAmount,
        CoinRewardSizeType CoinRewardSizeType)
    {
        public static MstStageEnhanceRewardParamModel Empty = new MstStageEnhanceRewardParamModel(
            MasterDataId.Empty,
            EnhanceQuestMinThresholdScore.Empty,
            ItemAmount.Empty,
            CoinRewardSizeType.Small);
    }
}
